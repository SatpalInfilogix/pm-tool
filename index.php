<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/db.php';
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

    <?php
    $userProfile = userProfile();
    $userId = $userProfile['id'];
    $role = $userProfile['role'];
    ?>

    <div class="col-xl-12">
        <div class="row">
            <?php if ($role === 'admin' || $role === 'hr'): ?>
                <!-- Admin/HR Cards -->
                <div class="col-md-4">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium fs-3">Employees</p>
                                    <h4 class="mb-0">
                                        <?php
                                        $result = mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'employee'");
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
            <?php endif; ?>
        </div> <!-- End Admin/HR Row -->
    </div> <!-- End Admin/HR Column -->



    <?php if ($userProfile['role'] === 'employee'): ?>
        <?php
        $userId = $userProfile['id'];
        $currentMonth = date('Y-m');
        $today = date('Y-m-d');
        $endOfWeek = date('Y-m-d', strtotime('+7 days'));

        // Attendance count
        $stmt1 = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
        $stmt1->bind_param("is", $userId, $currentMonth);
        $stmt1->execute();
        $stmt1->bind_result($attendanceCount);
        $stmt1->fetch();
        $stmt1->close();

        // Project count
        $assignedProjectCount = 0;
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'employee_projects'");
        if (mysqli_num_rows($checkTable)) {
            $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT p.id)
        FROM projects p
        JOIN employee_projects ep ON p.id = ep.project_id
        WHERE ep.employee_id = ?
    ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($assignedProjectCount);
            $stmt->fetch();
            $stmt->close();
        }

        // Milestones due in next 7 days
        $completedMilestoneCount = 0;
        $checkMilestoneTable = mysqli_query($conn, "SHOW TABLES LIKE 'project_milestones'");
        if (mysqli_num_rows($checkMilestoneTable)) {
            $stmt = $conn->prepare("
        SELECT COUNT(*) FROM project_milestones pm
        JOIN projects p ON pm.project_id = p.id
        JOIN employee_projects ep ON p.id = ep.project_id
        WHERE ep.employee_id = ? AND pm.status = 'completed'
    ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($completedMilestoneCount);
            $stmt->fetch();
            $stmt->close();
        }

        ?>

        <div class="col-xl-12">
            <div class="row">
                <!-- Attendance -->
                <div class="col-md-4">
                    <div class="card mini-stats-wid shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium fs-4">Days Attended</p>
                                    <h4 class="mb-0"><?= $attendanceCount ?></h4>
                                </div>
                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                        <span class="avatar-title"><i class="bx bx-calendar-check fs-2"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Projects -->
                <div class="col-md-4">
                    <div class="card mini-stats-wid shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium fs-4">Projects</p>
                                    <h4 class="mb-0"><?= $assignedProjectCount ?></h4>
                                </div>
                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                        <span class="avatar-title"><i class="bx bx-briefcase fs-2"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Upcoming Milestones -->
                <!-- Completed Tasks -->
                <div class="col-md-4">
                    <div class="card mini-stats-wid shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium fs-4">Completed Tasks</p>
                                    <h4 class="mb-0"><?= $completedMilestoneCount ?></h4>
                                </div>
                                <div class="flex-shrink-0 align-self-center">
                                    <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                        <span class="avatar-title"><i class="bx bx-check-double fs-2"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

<?php if ($userProfile['role'] === 'team leader'): ?>
    <?php
    $userId = $userProfile['id'];

    // 1. Count employees under this leader
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE assigned_leader_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($teamMembersCount);
    $stmt->fetch();
    $stmt->close();

    // 2. Count projects assigned to the team leader
    $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE team_leader_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($leaderProjectCount);
    $stmt->fetch();
    $stmt->close();

    // 3. Count completed milestones for leader's projects
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM project_milestones pm 
        JOIN projects p ON pm.project_id = p.id 
        WHERE p.team_leader_id = ? AND pm.status = 'completed'
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($completedMilestones);
    $stmt->fetch();
    $stmt->close();
    ?>

    <div class="col-xl-12">
        <div class="row">
            <!-- Team Members -->
            <div class="col-md-4">
                <div class="card mini-stats-wid shadow">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium fs-4">Team Members</p>
                                <h4 class="mb-0"><?= $teamMembersCount ?></h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                                    <span class="avatar-title"><i class="bx bx-group fs-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects -->
            <div class="col-md-4">
                <div class="card mini-stats-wid shadow">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium fs-4">My Projects</p>
                                <h4 class="mb-0"><?= $leaderProjectCount ?></h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title"><i class="bx bx-briefcase fs-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Milestones -->
            <div class="col-md-4">
                <div class="card mini-stats-wid shadow">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium fs-4">Completed Tasks</p>
                                <h4 class="mb-0"><?= $completedMilestones ?></h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                                    <span class="avatar-title"><i class="bx bx-check-double fs-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>




    <!-- Milestone Alerts -->
    <?php
    $userProfile = userProfile();
    $userId = $userProfile['id'];
    $userRole = $userProfile['role'];
    $currentDate = date('Y-m-d');

    // SQL base
    $sql = "SELECT 
            pm.id, 
            pm.milestone_name, 
            pm.due_date, 
            pm.status, 
            p.name AS project_name";

    // Only for admin/hr/team leader, add GROUP_CONCAT for multiple employees
    if ($userRole !== 'employee') {
        $sql .= ", GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS employee_names";
    }

    $sql .= " FROM project_milestones pm
          JOIN projects p ON pm.project_id = p.id";

    // Join only if not employee
    if ($userRole !== 'employee') {
        $sql .= " LEFT JOIN employee_projects ep ON ep.project_id = p.id
              LEFT JOIN users u ON u.id = ep.employee_id";
    } else {
        $sql .= " JOIN employee_projects ep ON ep.project_id = p.id
              WHERE ep.employee_id = $userId AND pm.due_date <= '$currentDate'";
    }

    // Add due date filter for non-employee
    if ($userRole !== 'employee') {
        $sql .= " WHERE pm.due_date <= '$currentDate'";
    }

    // Grouping to avoid duplicate rows for same milestone
    $sql .= " GROUP BY pm.id";

    // Run query
    $query = mysqli_query($conn, $sql);
    $milestones = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $milestones[] = $row;
        }
    }

    // Check if any milestone is due (not completed or not_started)
    $hasDueMilestone = false;
    foreach ($milestones as $row) {
        $status = $row['status'] ?? '';
        if ($status !== 'not_started' && $status !== 'completed') {
            $hasDueMilestone = true;
            break;
        }
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

        // Redirect based on role
        $redirectUrl = 'milestones/index.php';
        if (in_array($userRole, ['admin', 'hr', 'team leader'])) {
            $redirectUrl = 'milestones/edit.php?id=' . $row['id'];
        }
        ?>
        <div class="alert alert-<?php echo $alertClass; ?> mb-3"
            role="alert"
            style="cursor: pointer;"
            onclick="window.location.href='<?php echo $redirectUrl; ?>'">
            <?php echo htmlspecialchars($row['project_name']); ?>'s milestone
            <strong><?php echo htmlspecialchars($row['milestone_name']); ?></strong> is due on
            <strong><?php echo htmlspecialchars($row['due_date']); ?></strong>
            <?php if ($userRole !== 'employee' && !empty($row['employee_names'])): ?>
                by <strong><?php echo htmlspecialchars($row['employee_names']); ?></strong>
                <?php endif; ?>.
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
                                        $statusLabel = "-";
                                        $badgeClass = "secondary";
                                        $workedHours = "-";
                                    }

                                ?>
                                    <tr style="cursor:pointer;" onclick="window.location.href='attendance/index.php';">
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                        <td><?php echo $inTimeDisplay; ?></td>
                                        <td><?php echo $outTimeDisplay; ?></td>
                                        <td>
                                            <?php if ($statusLabel !== "-"): ?>
                                                <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
                                            <?php else: ?>
                                                <span>-</span>
                                            <?php endif; ?>
                                        </td>
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
                                    <th>Pending Requests</th>
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


    <!-- Employee leaves -->
    <?php if ($userProfile['role'] === 'employee') { ?>
        <?php
        $currentMonth = date('Y-m');
        $userId = $userProfile['id'];

        // Get all attendance records of current month
        $attendanceSql = "SELECT date, in_time, out_time, note 
                      FROM attendance 
                      WHERE employee_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
        $stmt = $conn->prepare($attendanceSql);
        $stmt->bind_param("is", $userId, $currentMonth);
        $stmt->execute();
        $result = $stmt->get_result();

        $attendanceData = [];
        while ($row = $result->fetch_assoc()) {
            $attendanceData[$row['date']] = $row;
        }

        // Initialize counters
        $presentCount = 0;
        $shortLeaveCount = 0;
        $halfDayCount = 0;
        $absentCount = 0;

        // Generate all days of the current month
        $daysInMonth = date('t');
        $year = date('Y');
        $month = date('m');
        $today = date('Y-m-d');
        ?>

        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        Your Attendance - <?= date('F Y'); ?>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Status</th>
                                    <th>Working Hours</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                for ($day = $daysInMonth; $day >= 1; $day--) {
                                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);

                                    // Skip future dates
                                    if ($date > $today) {
                                        continue;
                                    }

                                    $weekday = date('l', strtotime($date));
                                    echo "<tr>";
                                    echo "<td>$day</td>";
                                    echo "<td>" . date('d M Y (D)', strtotime($date)) . "</td>";

                                    if ($weekday === 'Saturday' || $weekday === 'Sunday') {
                                        echo "<td colspan='5' class='text-center text-muted'>Rest Day</td>";
                                        echo "<td>-</td>";
                                    } elseif (isset($attendanceData[$date])) {
                                        $record = $attendanceData[$date];
                                        $in_time = $record['in_time'];
                                        $out_time = $record['out_time'];
                                        $note = $record['note'];

                                        // In/Out display
                                        echo "<td>" . (!empty($in_time) ? date('h:i A', strtotime($in_time)) : '-') . "</td>";
                                        echo "<td>" . (!empty($out_time) ? date('h:i A', strtotime($out_time)) : '-') . "</td>";

                                        // Status and working hours
                                        if (!empty($in_time) && !empty($out_time)) {
                                            $in = strtotime($in_time);
                                            $out = strtotime($out_time);
                                            if ($out < $in) {
                                                $out += 24 * 3600; // Overnight shift
                                            }

                                            $seconds = $out - $in;
                                            $hours = floor($seconds / 3600);
                                            $minutes = floor(($seconds % 3600) / 60);
                                            $workedHours = "{$hours}h {$minutes}m";

                                            // Status calculation
                                            if ($hours >= 8) {
                                                $derivedStatus = "Present";
                                                $badgeClass = "success";
                                                $presentCount++;
                                            } elseif ($hours >= 6) {
                                                $derivedStatus = "Short Leave";
                                                $badgeClass = "secondary";
                                                $shortLeaveCount++;
                                            } elseif ($hours >= 3) {
                                                $derivedStatus = "Half Day";
                                                $badgeClass = "info";
                                                $halfDayCount++;
                                            } else {
                                                $derivedStatus = "Absent";
                                                $badgeClass = "danger";
                                                $absentCount++;
                                            }

                                            echo "<td><span class='badge bg-$badgeClass'>$derivedStatus</span></td>";
                                            echo "<td>$workedHours</td>";
                                        } else {
                                            echo "<td><span class='badge bg-danger'>Absent</span></td>";
                                            echo "<td>-</td>";
                                            $absentCount++;
                                        }

                                        echo "<td>" . htmlspecialchars($note) . "</td>";
                                    } else {
                                        // No record = Absent
                                        echo "<td>-</td><td>-</td>";
                                        echo "<td><span class='badge bg-danger'>Absent</span></td>";
                                        echo "<td>-</td>";
                                        echo "<td>-</td>";
                                        $absentCount++;
                                    }
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <strong class="d-block mb-2">Summary:</strong>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <span class="badge bg-success">Present: <?= $presentCount ?></span>
                            <span class="badge bg-secondary">Short Leave: <?= $shortLeaveCount ?></span>
                            <span class="badge bg-info">Half Day: <?= $halfDayCount ?></span>
                            <span class="badge bg-danger">Absent: <?= $absentCount ?></span>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    <?php } ?>



</div> <!-- end row -->

<!-- DataTable Script -->
<script>
    $(document).ready(function() {
        $('#monthly-attendance-table').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthMenu: [10, 25, 50, 100],
            autoWidth: false
        });
    });

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