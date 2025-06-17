<?php
ob_start();
require_once '../includes/header.php';

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['milestone_request'])) {
    require_once '../includes/db.php';

    $userProfile = userProfile(); // 🔥 moved here inside POST block

    $milestoneId = (int) $_POST['milestone_id'];
    $newDueDate = $_POST['new_due_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $employeeId = $userProfile['id'];

    // Step 1: Fetch milestone and project name
    $sql = "SELECT pm.milestone_name, p.name AS project_name
            FROM project_milestones pm
            JOIN projects p ON pm.project_id = p.id
            WHERE pm.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $milestoneId);
    $stmt->execute();
    $result = $stmt->get_result();
    $milestone = $result->fetch_assoc();

    if (!$milestone) {
        $_SESSION['toast'] = "Milestone not found.";
        header("Location: /pm-tool/milestones/edit.php?id=" . $milestoneId);
        exit;
    }

    // Step 2: Insert request
    $insertSql = "INSERT INTO milestone_requests (milestone_id, employee_id, new_due_date, reason, created_at)
                  VALUES (?, ?, ?, ?, NOW())";
    $reqStmt = $conn->prepare($insertSql);
    $reqStmt->bind_param("iiss", $milestoneId, $employeeId, $newDueDate, $reason); // 🔥 fixed bind count

    if (!$reqStmt->execute()) {
        $_SESSION['toast'] = "Database error: " . $reqStmt->error;
        header("Location: /pm-tool/milestones/edit.php?id=" . $milestoneId);
        exit;
    }

    // Step 3: Send notification to all admins
    $projectName = $milestone['project_name'];
    $milestoneName = $milestone['milestone_name'];

    $message = "Employee {$userProfile['name']} requested a new due date for milestone '{$milestoneName}' in project '{$projectName}' to {$newDueDate}.";
    $link = "milestones/edit.php?id={$milestoneId}";

    $notifySql = "INSERT INTO notifications (user_id, message, link)
                  SELECT id, ?, ? FROM users WHERE role = 'admin'";
    $notifyStmt = $conn->prepare($notifySql);
    $notifyStmt->bind_param("ss", $message, $link);
    $notifyStmt->execute();

    $_SESSION['toast'] = "Request submitted successfully.";
    header("Location: /pm-tool/milestones/edit.php?id=" . $milestoneId);
    exit;
}
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Milestones</h4>
            <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr' || $userProfile['role'] === 'team leader') { ?>
                <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Add Milestone</a>
            <?php } ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive"> <!-- Added for responsiveness -->

            <?php
            $sql = "SELECT 
    pm.*, 
    p.name as project_name, 
    p.hourly_rate, 
    GROUP_CONCAT(u.name SEPARATOR ', ') AS assigned_employees
FROM project_milestones pm
JOIN projects p ON pm.project_id = p.id
LEFT JOIN employee_projects ep ON p.id = ep.project_id
LEFT JOIN users u ON ep.employee_id = u.id
WHERE 1";

            if ($userProfile['role'] === 'employee') {
                $userId = $userProfile['id'];
                $sql .= " AND ep.employee_id = $userId";
            }

            $sql .= " GROUP BY pm.id";


            $query = mysqli_query($conn, $sql);
            $milestones = mysqli_fetch_all($query, MYSQLI_ASSOC);

            ?>



            <table class="table table-sm" id="milestoneTable">
                <thead>
                    <th>#</th>
                    <th>Project Name</th>
                    <th>Assigned Employees</th>
                    <th>Name</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Status</th>
                    <th>Action</th>
                </thead>
                <tbody>

                    <?php
                    $alertedMilestones = [];

                    $alertQuery = mysqli_query($conn, "SELECT DISTINCT SUBSTRING_INDEX(link, '=', -1) AS milestone_id FROM notifications WHERE user_id = '$userId' AND read_status = 0 AND link LIKE 'milestones/edit.php?id=%'");

                    if ($alertQuery && mysqli_num_rows($alertQuery) > 0) {
                        while ($row = mysqli_fetch_assoc($alertQuery)) {
                            $alertedMilestones[] = (int)$row['milestone_id'];
                        }
                    }

                    foreach ($milestones as $key => $row) {
                        $highlightClass = in_array((int)$row['id'], $alertedMilestones) ? 'table-warning fw-bold' : '';
                    ?>
                        <tr class="<?php echo $highlightClass; ?>">
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo $row['project_name']; ?></td>
                            <td><?php echo $row['assigned_employees'] ?: 'N/A'; ?></td>
                            <td><?php echo $row['milestone_name']; ?></td>
                            <td><?php echo $row['due_date']; ?></td>
                            <td><?php echo $row['amount'] ? number_format($row['amount'], 2) : '-'; ?></td>
                            <td><?php echo $row['currency_code']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($row['status'] == 'completed') ? 'success' : (($row['status'] == 'in_progress') ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr' || $userProfile['role'] === 'team leader') { ?>

                                    <a href="./edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="bx bx-edit fs-5"></i>
                                    </a>

                                    <button class="btn btn-danger delete-btn btn-sm" data-table-name="project_milestones" data-id="<?php echo $row['id']; ?>">
                                        <i class="bx bx-trash fs-5"></i>
                                    </button>
                                <?php } ?>

                                <?php if ($row['status'] == 'completed') { ?>
                                    <a href="download.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fa fa-download"></i>
                                    </a>
                                <?php } ?>

                                <?php if ($userProfile['role'] === 'employee' && $row['status'] !== 'completed') { ?>
                                    <button
                                        type="button"
                                        class="btn btn-info btn-sm request-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#milestoneRequestModal"
                                        data-id="<?= $row['id'] ?>"
                                        data-due-date="<?= $row['due_date'] ?>">
                                        <i class="bx bx-time"></i> Request Change
                                    </button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Modal HTML -->
<!-- Modal HTML -->
<div class="modal fade" id="milestoneRequestModal" tabindex="-1" aria-labelledby="milestoneRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="" id="milestoneRequestForm">
            <input type="hidden" name="milestone_id" id="modal_milestone_id">
            <input type="hidden" name="milestone_request" value="1">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request New Due Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>


                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_date" class="form-label">New Date</label>
                        <input type="date" class="form-control" name="new_due_date" id="new_date" autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" id="reason" rows="3" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

</div>

<script>
    $(document).ready(function() {
        // Remove this line ↓ (no longer needed)
        // $('#new_date').datepicker({...});

        $('#milestoneTable').DataTable();

        $('#milestoneRequestModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const milestoneId = button.data('id');

            const modal = $(this);
            modal.find('#modal_milestone_id').val(milestoneId);
            modal.find('#new_date').val(''); // Reset the input
        });
    });
</script>



<?php require_once '../includes/footer.php';
ob_end_flush(); ?>