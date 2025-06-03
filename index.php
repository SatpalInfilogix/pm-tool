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
    $sql = "SELECT pm.milestone_name, pm.due_date, pm.status, p.name AS project_name
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

    <?php foreach ($milestones as $row): ?>
        <?php
        $status = $row['status'];
        if ($status === 'not_started' || $status === 'completed') continue;
        $alertClass = ($status === 'in_progress') ? 'warning' : 'secondary';
        ?>
        <h3>Due Milestones:</h3>
        <div class="alert alert-<?php echo $alertClass; ?> mb-3" role="alert">
            <?php echo htmlspecialchars($row['project_name']); ?>'s milestone
            <strong><?php echo htmlspecialchars($row['milestone_name']); ?></strong> is due on
            <strong><?php echo htmlspecialchars($row['due_date']); ?></strong>.
        </div>
    <?php endforeach; ?>

    <!-- Attendance Section -->
    <?php
    $attDate = date('Y-m-d');
    $attQuery = "SELECT a.employee_id, u.name AS employee_name, a.status, a.in_time, a.out_time
                 FROM attendance a
                 JOIN users u ON a.employee_id = u.id
                 WHERE a.date = '$attDate'
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
                                <th>Total Hours</th> <!-- New header -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            while ($row = mysqli_fetch_assoc($attResult)):
                                $status = $row['status'];
                                $statusLabel = ucfirst(str_replace('_', ' ', $status));
                                $badgeClass = 'dark';
                                $workedHours = '-';
                                $inTimeDisplay = '-';
                                $outTimeDisplay = '-';

                                $inTime = strtotime($row['in_time']);
                                $outTime = strtotime($row['out_time']);
                                $isInTimeValid = $inTime !== false && $row['in_time'] !== null && $row['in_time'] !== '00:00:00';
                                $isOutTimeValid = $outTime !== false && $row['out_time'] !== null && $row['out_time'] !== '00:00:00';

                                if (!$isInTimeValid || !$isOutTimeValid) {
                                    // No valid times – default to Absent
                                    $statusLabel = "Absent";
                                    $badgeClass = 'danger';
                                } else {
                                    $inTimeDisplay = date("h:i A", $inTime);
                                    $outTimeDisplay = date("h:i A", $outTime);
                                    $seconds = $outTime - $inTime;
                                    $hours = floor($seconds / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    $workedHours = "{$hours}h {$minutes}m";
                                    // Override status based on hours
                                    if ($hours >= 8) {
                                        $statusLabel = "Present";
                                        $badgeClass = 'success';
                                    } elseif ($hours >= 3 && $hours < 6) {
                                        $statusLabel = "Half Day";
                                        $badgeClass = 'info';
                                    } elseif ($hours >= 6 && $hours < 8) {
                                        $statusLabel = "Short Leave";
                                        $badgeClass = 'secondary';
                                    } elseif ($hours < 3) {
                                        $statusLabel = "Absent";
                                        $badgeClass = 'danger';
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                    <td><?php echo $inTimeDisplay; ?></td>
                                    <td><?php echo $outTimeDisplay; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $badgeClass; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $workedHours; ?></td> <!-- New total hours column -->
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

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
</script>

<?php require_once './includes/footer.php'; ?>