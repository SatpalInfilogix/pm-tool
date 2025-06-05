<?php
ob_start();
require_once '../includes/db.php';
require_once '../includes/header.php';

$userRole = $userProfile['role'] ?? null;
$userId = (int)($userProfile['id'] ?? 0);

$statusFilter = $_GET['status'] ?? '';
$employeeFilter = $_GET['employee'] ?? '';
$dateRange = $_GET['dateRange'] ?? '';

$filters = [];

if ($userRole === 'admin' || $userRole === 'hr') {
    $query = "SELECT a.id, a.date, u.name AS employee_name, a.note, a.in_time, a.out_time, u.id AS employee_id
          FROM attendance a
          JOIN users u ON a.employee_id = u.id";

    if (!empty($employeeFilter) && is_numeric($employeeFilter)) {
        $employeeId = (int)$employeeFilter;
        $filters[] = "u.id = $employeeId";
    }
} else {
    // Normal users see only their own attendance
    $query = "SELECT a.id, a.date, u.name AS employee_name, a.note, a.in_time, a.out_time, u.id AS employee_id
              FROM attendance a
              JOIN users u ON a.employee_id = u.id
              WHERE u.id = $userId";
}

if (!empty($dateRange) && strpos($dateRange, ' to ') !== false) {
    [$startDate, $endDate] = explode(' to ', $dateRange);
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    $filters[] = "a.date BETWEEN '$startDate' AND '$endDate'";
}

if (!empty($filters)) {
    if (strpos($query, 'WHERE') !== false) {
        $query .= ' AND ' . implode(' AND ', $filters);
    } else {
        $query .= ' WHERE ' . implode(' AND ', $filters);
    }
}

$query .= " ORDER BY a.date DESC, u.name ASC";

$result = mysqli_query($conn, $query);

$filteredRows = [];

while ($row = mysqli_fetch_assoc($result)) {
    $inTimeDisplay = ($row['in_time'] === null || trim($row['in_time']) === '') ? '-' : date('h:i A', strtotime($row['in_time']));
    $outTimeDisplay = ($row['out_time'] === null || trim($row['out_time']) === '') ? '-' : date('h:i A', strtotime($row['out_time']));

    $isInTimeValid = ($row['in_time'] !== null && trim($row['in_time']) !== '');
    $isOutTimeValid = ($row['out_time'] !== null && trim($row['out_time']) !== '');

    $inTime = $isInTimeValid ? strtotime($row['in_time']) : null;
    $outTime = $isOutTimeValid ? strtotime($row['out_time']) : null;

    // Avoid displaying 12:00 AM/PM for zero timestamps
    if ($isInTimeValid && $inTime > 0) {
        $inTimeDisplay = date("h:i A", $inTime);
    } else {
        $inTimeDisplay = '-';
    }

    if ($isOutTimeValid && $outTime > 0) {
        $outTimeDisplay = date("h:i A", $outTime);
    } else {
        $outTimeDisplay = '-';
    }

    $derivedStatus = "Absent";
    $badgeClass = "danger";
    $workedHours = '-';

    if ($isInTimeValid && $isOutTimeValid && $inTime > 0 && $outTime > 0) {
        $seconds = $outTime - $inTime;
        if ($seconds > 0) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $workedHours = "{$hours}h {$minutes}m";

            if ($hours >= 8) {
                $derivedStatus = "Present";
                $badgeClass = "success";
            } elseif ($hours >= 6) {
                $derivedStatus = "Short Leave";
                $badgeClass = "secondary";
            } elseif ($hours >= 3) {
                $derivedStatus = "Half Day";
                $badgeClass = "info";
            } else {
                $derivedStatus = "Absent";
                $badgeClass = "danger";
            }
        }
    }

    // Normalize status for filtering
    $normalizedDerivedStatus = strtolower(str_replace(' ', '_', $derivedStatus));

    if (empty($statusFilter) || $normalizedDerivedStatus === $statusFilter) {
        $row['derived_status'] = $derivedStatus;
        $row['badge_class'] = $badgeClass;
        $row['worked_hours'] = $workedHours;
        $row['in_time_display'] = $inTimeDisplay;
        $row['out_time_display'] = $outTimeDisplay;

        $filteredRows[] = $row;
    }
}
?>

<body>
    <div class="container-fluid ">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box pb-4 d-sm-flex align-items-center justify-content-between">
                    <h4>Attendance Records</h4>
                    <?php if ($userRole === 'admin' || $userRole === 'hr'): ?>
                        <a href="./form.php" class="btn btn-primary d-flex">
                            <i class="bx bx-plus me-1 fs-5"></i>Add Attendance
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form id="dateFilterForm" class="d-flex gap-2 align-items-end mb-3" method="GET">
            <div>
                <label for="dateRange">Date Range:</label>
                <input type="text" class="form-control" name="dateRange" id="dateRange" autocomplete="off"
                    value="<?php echo htmlspecialchars($dateRange); ?>">
            </div>

            <?php if ($userRole === 'admin' || $userRole === 'hr'): ?>
                <div>
                    <label for="employeeFilter">Employee:</label>
                    <select class="form-control" id="employeeFilter" name="employee">
                        <option value="">All</option>
                        <?php
                        $employeeQuery = mysqli_query($conn, "SELECT id, name FROM users WHERE role != 'admin' ORDER BY name");
                        while ($emp = mysqli_fetch_assoc($employeeQuery)) {
                            $selected = ($employeeFilter == $emp['id']) ? 'selected' : '';
                            echo '<option value="' . (int)$emp['id'] . '" ' . $selected . '>' . htmlspecialchars($emp['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <label for="statusFilter">Status:</label>
                <select class="form-control" id="statusFilter" name="status">
                    <option value="">All</option>
                    <?php
                    $statuses = ['present', 'absent', 'half_day', 'short_leave'];
                    foreach ($statuses as $status) {
                        $selected = ($statusFilter === $status) ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>" . ucfirst(str_replace('_', ' ', $status)) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">Filter</button>
            <a class="btn btn-success"
                href="export.php?<?php echo http_build_query([
                                        'status' => $statusFilter,
                                        'employee' => $employeeFilter,
                                        'dateRange' => $dateRange
                                    ]); ?>">
                Export
            </a>
        </form>

        <div class="card">
            <div class="card-body">
                <table id="attendanceTable" class="table table-bordered table-striped mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Employee Name</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>Status</th>
                            <th>Working Hours</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($filteredRows as $row): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                <td><?php echo $row['in_time_display']; ?></td>
                                <td><?php echo $row['out_time_display']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars($row['badge_class']); ?>" title="<?php echo htmlspecialchars($row['worked_hours']); ?>">
                                        <?php echo htmlspecialchars($row['derived_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['worked_hours']); ?></td>
                                <td><?php echo htmlspecialchars($row['note']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Apply',
                    cancelLabel: 'Clear'
                },
                opens: 'left'
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            $('#attendanceTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthMenu: [10, 25, 50, 100],
                autoWidth: false
            });
        });
    </script>

</body>

</html>

<?php require_once '../includes/footer.php'; ?>