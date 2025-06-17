<?php
ob_start();
require_once '../includes/header.php';
$userProfile = userProfile();
$userId = $userProfile['id'];
$userRole = $userProfile['role'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && in_array($userRole, ['admin', 'hr'])) {
    $leaveId = (int) $_POST['leave_id'];
    $newStatus = $_POST['new_status'];

    $updateQuery = "UPDATE leaves SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newStatus, $leaveId);
    if ($stmt->execute()) {
        // Get employee ID and leave details
        $detailsQuery = "SELECT leaves.*, users.name, users.id as user_id FROM leaves JOIN users ON users.id = leaves.employee_id WHERE leaves.id = ?";
        $stmtDetails = $conn->prepare($detailsQuery);
        $stmtDetails->bind_param("i", $leaveId);
        $stmtDetails->execute();
        $detailsResult = $stmtDetails->get_result();
        $leaveDetails = $detailsResult->fetch_assoc();

        if ($leaveDetails) {
            $message = "Your leave request from {$leaveDetails['start_date']} to {$leaveDetails['end_date']} has been " . ucfirst($newStatus);
            $link = BASE_URL . "/leaves/index.php";
            $empId = $leaveDetails['user_id']; // ✅ This is the employee ID

            $notify = $conn->prepare("INSERT INTO notifications (user_id, message, link, created_at) VALUES (?, ?, ?, NOW())");
            $notify->bind_param("iss", $empId, $message, $link);
            $notify->execute();
        }
    }
    header("Location: index.php");
    exit();
}
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Leaves</h4>
            <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Apply Leave</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive"> <!-- Added for responsiveness -->
            <?php
            if (in_array($userRole, ['admin', 'hr'])) {
                $leavesQuery = "SELECT leaves.*, users.name AS employee_name
                    FROM leaves
                    JOIN users ON users.id = leaves.employee_id
                    ORDER BY leaves.id DESC";
            } else {
                $leavesQuery = "SELECT leaves.*, users.name AS employee_name
                    FROM leaves
                    JOIN users ON users.id = leaves.employee_id
                    WHERE leaves.employee_id = $userId
                    ORDER BY leaves.id DESC";
            }


            $leavesResult = mysqli_query($conn, $leavesQuery);
            $leaves = [];
            while ($row = mysqli_fetch_assoc($leavesResult)) {
                $leaves[] = $row;
            }
            ?>

            <table class="table table-bordered table-striped" id="leavesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                            <th>Name</th>
                        <?php endif; ?>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $key => $row): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($row['leave_type']) ?></td>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td>
                                <?php
                                $status = ucfirst(strtolower(trim($row['status'])));
                                $badgeClass = match ($status) {
                                    'Approved' => 'success',
                                    'Rejected' => 'danger',
                                    'Pending' => 'secondary',
                                    default => 'dark'
                                };
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>"><?= $status ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>

                            <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                                <td>
                                    <!-- Edit and Delete buttons -->
                                    <a href='./edit.php?id=<?= $row['id'] ?>' class="btn btn-primary btn-sm"><i class="bx bx-edit fs-5"></i></a>
                                    <button class="btn btn-danger btn-sm delete-btn" data-table-name="leaves" data-id="<?= $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>

                                    <!-- Approve/Reject buttons -->
                                    <?php if (strtolower($row['status']) === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="new_status" value="approved">
                                            <button class="btn btn-success btn-sm" name="update_status">Approve</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="new_status" value="rejected">
                                            <button class="btn btn-danger btn-sm" name="update_status">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    $(document).ready(function() {
        $('#leavesTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthMenu: [10, 25, 50, 100],
            autoWidth: false
        });
    });
</script>
<?php
ob_end_flush();
require_once '../includes/footer.php'; ?>