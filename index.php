<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/db.php'; // Make sure DB connection is available
?>
<?php if (isset($_SESSION['toast'])): ?>
    <script>
        // alert("<?= $_SESSION['toast'] ?>");
    </script>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<?php require_once './includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-xl-12">
        <div class="row">
            <!-- Employees -->
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium fs-3">Employees</p>
                                <h4 class="mb-0">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT COUNT(*) FROM users");
                                    $row = mysqli_fetch_array($result);
                                    echo $row[0];
                                    ?>
                                </h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title"><i class="bx bxs-user fs-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects -->
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium fs-3">Projects</p>
                                <h4 class="mb-0">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT COUNT(*) FROM projects");
                                    $row = mysqli_fetch_array($result);
                                    echo $row[0];
                                    ?>
                                </h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title"><i class="bx bx-briefcase-alt-2 fs-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients -->
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium fs-3">Clients</p>
                                <h4 class="mb-0">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT COUNT(*) FROM clients");
                                    $row = mysqli_fetch_array($result);
                                    echo $row[0];
                                    ?>
                                </h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title"><i class="bx bxs-user fs-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Milestone Alerts -->
    <?php
    $currentDate = date('Y-m-d');
    $sql = "SELECT pm.id, pm.milestone_name, pm.due_date, pm.status, p.name AS project_name
        FROM project_milestones pm
        JOIN projects p ON pm.project_id = p.id
        WHERE pm.due_date <= '$currentDate'";
    $query = mysqli_query($conn, $sql);
    $milestones = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $milestones[] = $row;
        }
    }
    ?>

    <?php
    $hasDueMilestone = false;
    foreach ($milestones as $row) {
        $status = $row['status'] ?? '';
        if ($status === 'not_started' || $status === 'completed') continue;
        $hasDueMilestone = true;
        break; // We only need to know that at least one exists
    }
    ?>

    <?php if ($hasDueMilestone): ?>
        <h4><b>Due Milestones:</b></h4>
    <?php endif; ?>

    <?php foreach ($milestones as $row): ?>
        <?php
        $status = $row['status'] ?? '';
        if ($status === 'not_started' || $status === 'completed') continue;
        $alertClass = ($status === 'in_progress') ? 'warning' : 'secondary';
        ?>
        <div class="alert alert-<?php echo $alertClass; ?> mb-3"
            role="alert"
            style="cursor: pointer;"
            onclick="window.location.href='milestones/edit.php?id=<?= $row['id'] ?>'">
            <?php echo htmlspecialchars($row['project_name']); ?>'s milestone
            <strong><?php echo htmlspecialchars($row['milestone_name']); ?></strong> is due on
            <strong><?php echo htmlspecialchars($row['due_date']); ?></strong>.
        </div>
    <?php endforeach; ?>



    <!-- Attendance Section -->
    <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>

        <?php
        $attDate = date('Y-m-d');
        $attQuery = "SELECT a.employee_id, u.name AS employee_name, a.in_time, a.out_time
 FROM attendance a
 JOIN users u ON a.employee_id = u.id
 WHERE a.date = '$attDate' AND u.role != 'admin'
 ORDER BY u.name ASC";

        $attResult = mysqli_query($conn, $attQuery);
        ?>

        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Today's Attendance (<?php echo $attDate; ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="daily-attendance">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Status</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($attResult)):
                                    $statusLabel = 'Absent';
                                    $badgeClass = 'danger';
                                    $workedHours = '-';

                                    // Handle empty/zero times properly
                                    $inTimeRaw = $row['in_time'];
                                    $outTimeRaw = $row['out_time'];

                                    $inTime = ($inTimeRaw && $inTimeRaw !== '00:00:00') ? strtotime($inTimeRaw) : false;
                                    $outTime = ($outTimeRaw && $outTimeRaw !== '00:00:00') ? strtotime($outTimeRaw) : false;

                                    $inTimeDisplay = $inTime ? date("h:i A", $inTime) : '-';
                                    $outTimeDisplay = $outTime ? date("h:i A", $outTime) : '-';

                                    $hours = 0; // always initialize
                                    if ($inTime && $outTime) {
                                        if ($outTime >= $inTime) {
                                            $seconds = $outTime - $inTime;
                                            $hours = floor($seconds / 3600);
                                            $minutes = floor(($seconds % 3600) / 60);
                                            $workedHours = "{$hours}h {$minutes}m";

                                            // Auto-assign status
                                            if ($hours >= 8) {
                                                $statusLabel = "Present";
                                                $badgeClass = 'success';
                                            } elseif ($hours >= 6) {
                                                $statusLabel = "Short Leave";
                                                $badgeClass = 'secondary';
                                            } elseif ($hours >= 3) {
                                                $statusLabel = "Half Day";
                                                $badgeClass = 'info';
                                            } else {
                                                $statusLabel = "Absent";
                                                $badgeClass = 'danger';
                                            }
                                        } else {
                                            $statusLabel = "Invalid Time";
                                            $badgeClass = 'warning';
                                            $workedHours = "-";
                                        }
                                    } elseif ($inTime && !$outTime) {
                                        $statusLabel = "In Progress";
                                        $badgeClass = "primary";
                                    }
                                ?>
                                    <tr style="cursor:pointer;" onclick="window.location.href='attendance/index.php';">
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                        <td><?php echo $inTimeDisplay; ?></td>
                                        <td><?php echo $outTimeDisplay; ?></td>
                                        <td><span class="badge bg-<?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span></td>
                                        <td><?php echo $workedHours; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>



    <!-- Leaves data -->
    <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>
        <?php
        $today = date('Y-m-d');
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        $allEmployeesQuery = "
SELECT 
    u.id AS employee_id,
    u.name AS employee_name,
    u.date_of_joining,

    -- Total leaves (all types, all statuses)
    (SELECT COUNT(*) FROM leaves WHERE employee_id = u.id) AS total_leaves,

    -- Current month leaves
    (SELECT COUNT(*) FROM leaves 
     WHERE employee_id = u.id 
       AND DATE_FORMAT(start_date, '%Y-%m') = '$currentMonth') AS current_month_leaves,

    -- Last month leaves
    (SELECT COUNT(*) FROM leaves 
     WHERE employee_id = u.id 
       AND DATE_FORMAT(start_date, '%Y-%m') = '$lastMonth') AS last_month_leaves,

    -- Pending leaves
    (SELECT COUNT(*) FROM leaves 
     WHERE employee_id = u.id 
       AND status = 'Pending') AS pending_leaves,

    -- Earned paid leaves: 1 per month worked, max 12/year
    LEAST(TIMESTAMPDIFF(MONTH, u.date_of_joining, CURDATE()), 12) AS earned_paid_leaves,

    -- Used paid leave days (sum of days)
    IFNULL((
        SELECT SUM(DATEDIFF(end_date, start_date) + 1)
        FROM leaves 
        WHERE employee_id = u.id 
          AND status = 'Approved'
          AND leave_type = 'Paid'
    ), 0) AS used_paid_leaves,

    -- Remaining paid leave days
    GREATEST(
        LEAST(TIMESTAMPDIFF(MONTH, u.date_of_joining, CURDATE()), 12) -
        IFNULL((
            SELECT SUM(DATEDIFF(end_date, start_date) + 1)
            FROM leaves 
            WHERE employee_id = u.id 
              AND status = 'Approved'
              AND leave_type = 'Paid'
        ), 0),
    0) AS remaining_paid_leaves

FROM users u
WHERE u.role != 'admin'
ORDER BY u.name ASC
";


        $result = mysqli_query($conn, $allEmployeesQuery);
        $employeeLeaves = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $employeeLeaves[] = $row;
            }
        }
        ?>

        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Employees Leaves</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="leaves-summary-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <th>Total Leaves</th>
                                    <th>Current Month</th>
                                    <th>Last Month</th>
                                    <th>Pending</th>
                                    <th>Earned Paid</th>
                                    <th>Joining Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employeeLeaves as $index => $emp): ?>
                                    <tr style="cursor:pointer;" onclick="window.location.href='leaves/index.php';">
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($emp['employee_name']) ?></td>
                                        <td><?= $emp['total_leaves'] ?></td>
                                        <td><?= $emp['current_month_leaves'] ?></td>
                                        <td><?= $emp['last_month_leaves'] ?></td>
                                        <td><?= $emp['pending_leaves'] ?></td>
                                        <td><?= $emp['earned_paid_leaves'] ?></td>
                                        <td><?= date('Y-m-d', strtotime($emp['date_of_joining'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>





</div> <!-- end row -->

<!-- DataTable Script -->
<script>
    $(document).ready(function() {
        $('#daily-attendance').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthMenu: [10, 25, 50, 100],
            autoWidth: false
        });
    });
    $(document).ready(function() {
        $('#leaves-summary-table').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthMenu: [10, 25, 50, 100],
            autoWidth: false
        });
    });
</script>

<?php require_once './includes/footer.php'; ?>