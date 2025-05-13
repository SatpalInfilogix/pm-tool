<?php
ob_start();
require_once '../includes/db.php';
require_once '../includes/header.php';

$userRole = $userProfile['role'] ?? null;
$userId = $userProfile['id'] ?? 0;
$validStatuses = ['present', 'absent', 'late', 'half_day', 'short_leave'];

$statusFilter = $_GET['status'] ?? '';
$employeeFilter = $_GET['employee'] ?? '';
$dateRange = $_GET['dateRange'] ?? '';

$filters = [];

if ($userRole === 'admin' || $userRole === 'hr') {
    $query = "SELECT a.id, a.date, u.name AS employee_name, a.status, a.note 
              FROM attendance a 
              JOIN users u ON a.employee_id = u.id";

    if (!empty($employeeFilter)) {
        $filters[] = "u.name = '" . mysqli_real_escape_string($conn, $employeeFilter) . "'";
    }
} else {
    $query = "SELECT a.id, a.date, u.name AS employee_name, a.status, a.note 
              FROM attendance a 
              JOIN users u ON a.employee_id = u.id 
              WHERE u.id = $userId";
}

if (!empty($statusFilter) && in_array($statusFilter, $validStatuses)) {
    $filters[] = "a.status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}

if (!empty($dateRange) && strpos($dateRange, 'to') !== false) {
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

// Export CSV only if export=1
if (isset($_GET["export"])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data.csv');

    $output = fopen("php://output", "w");
    fputcsv($output, array('Index', 'Date', 'Employee Name', 'Status', 'Note'));

    $index = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $index++,
            $row['date'],
            $row['employee_name'],
            ucfirst(str_replace('_', ' ', $row['status'])),
            $row['note']
        ]);
    }

    fclose($output);
    exit;
}



?>


<!DOCTYPE html>
<html>

<head>
</head>

<body>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
                <h4>Attendance Records</h4>
                <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>
                    <a href="./create.php" class="btn btn-primary d-flex">
                        <i class="bx bx-plus me-1 fs-5"></i>Add Attendance
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <form id="dateFilterForm" class="d-flex gap-2 align-items-end mb-3" method="GET">
        <div>
            <label for="dateRange">Date Range:</label>
            <input type="text" class="form-control" name="dateRange" id="dateRange" autocomplete="off"
                value="<?php echo htmlspecialchars($dateRange); ?>">
        </div>

        <?php if ($userRole === 'admin' || $userRole === 'hr') { ?>
            <div>
                <label for="employeeFilter">Employee:</label>
                <select class="form-control" id="employeeFilter" name="employee">
                    <option value="">All</option>
                    <?php
                    $employeeQuery = mysqli_query($conn, "SELECT DISTINCT name FROM users");
                    while ($emp = mysqli_fetch_assoc($employeeQuery)) {
                        $selected = ($employeeFilter === $emp['name']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($emp['name']) . '" ' . $selected . '>' . htmlspecialchars($emp['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
        <?php } ?>

        <div>
            <label for="statusFilter">Status:</label>
            <select class="form-control" id="statusFilter" name="status">
                <option value="">All</option>
                <?php
                $statuses = ['present', 'absent', 'late', 'half_day', 'short_leave'];
                foreach ($statuses as $status) {
                    $selected = ($statusFilter === $status) ? 'selected' : '';
                    echo "<option value=\"$status\" $selected>" . ucfirst(str_replace('_', ' ', $status)) . "</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <button type="submit" name="export" value="1" class="btn btn-success">Export</button>

    </form>


    <div class="card">
        <div class="card-body">
            <table id="attendanceTable" class="table table-bordered table-striped mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Employee Name</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php
                                                        echo ($row['status'] === 'present') ? 'success' : (($row['status'] === 'absent') ? 'danger' : (($row['status'] === 'late') ? 'warning' : (($row['status'] === 'half_day') ? 'info' : (($row['status'] === 'short_leave') ? 'secondary' : 'dark'))));
                                                        ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                </span>
                            </td>

                            <td><?php echo htmlspecialchars($row['note']); ?></td>
                        </tr>
                    <?php endwhile; ?>
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
                    cancelLabel: 'Clear'
                },
                opens: 'left'
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRange').on('cancel.daterangepicker', function() {
                $(this).val('');
            });

            const table = $('#attendanceTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthMenu: [10, 25, 50, 100],
                autoWidth: false
            });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const employeeName = $('#employeeFilter').val();
                const rowName = data[2];

                if (employeeName && employeeName !== rowName) {
                    return false;
                }

                const statusFilter = $('#statusFilter').val();
                const rowStatus = $('<div>').html(data[3]).text().trim().toLowerCase();
                const filterValue = statusFilter.toLowerCase();
                if (filterValue && filterValue !== rowStatus) {
                    return false;
                }


                return true;
            });

            $('#dateFilterForm').on('submit', function() {
                return true;
            });

            $('.delete-btn').on('click', function() {
                const id = $(this).data('id');
                if (!confirm("Are you sure you want to delete this record?")) return;

                $.post('delete.php', {
                    id
                }, function() {
                    alert('Record deleted successfully.');
                    location.reload();
                }).fail(function() {
                    alert('Failed to delete the record.');
                });
            });
        });
    </script>

</body>

</html>
<?php require_once '../includes/footer.php'; ?>