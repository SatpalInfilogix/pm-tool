<?php require_once './includes/header.php'; ?>
<?php
require_once './includes/db.php';
$userProfile = userProfile();
function getNotifications($userProfile)
{
    global $conn;
    $notifications = [];

    // Only show own notifications for employees
    if ($userProfile['role'] === 'employee') {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userProfile['id']);
    } else {
        // Admins and HR see all notifications
        $sql = "SELECT * FROM notifications ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
    }

    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }

    return $notifications;
}

$notifications = getNotifications($userProfile);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Notifications </h4>
            <span class="badge bg-info fs-6"><?php echo count($notifications); ?> New</span>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="container">
            <table class="table table-sm" id="notificationTable">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Date And Time</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($notifications as $noti): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($noti['message']); ?></td>
                            <td><?php echo htmlspecialchars($noti['created_at']); ?></td>
                            <td>
                                <?php
                                $link = $noti['link'];
                                if ($userProfile['role'] === 'employee') {
                                    if (strpos($noti['message'], 'assigned to a new project') !== false) {
                                        $link = BASE_URL . '/projects/index.php';
                                    }
                                }

                                if (!empty($noti['link'])): ?>
                                    <a href="<?php echo  ltrim($noti['link'], '/'); ?>" class="btn btn-sm btn-primary">View</a>
                                <?php else: ?>
                                    <span class="text-muted">No link</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#notificationTable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "lengthMenu": [10, 25, 50, 100],
            "autoWidth": false,
            "order": [
                [1, "desc"]
            ] // Sort by Date And Time desc by default
        });
    });
</script>

<?php require_once './includes/footer.php'; ?>