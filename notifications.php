<?php require_once './includes/header.php'; ?>
<?php
require_once './includes/db.php';

function getNotifications()
{
    global $conn;
    $sql = "SELECT * FROM notifications ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $notifications = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    return $notifications;
}
$notifications = getNotifications();
$extraNotifications = array_slice($notifications, 5);
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Notifications </h4>
            <span class="badge bg-info fs-6"><?php echo count($extraNotifications); ?> New</span>
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
                    <?php foreach ($extraNotifications as $noti): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($noti['message']); ?></td>
                            <td><?php echo htmlspecialchars($noti['created_at']); ?> </td>
                            <td>
                                <?php
                                $notificationLink = htmlspecialchars($noti['link']);
                                // Debugging: Check the actual link generated
                                if (strpos($notificationLink, 'http') !== 0) {
                                    $notificationLink = BASE_URL . $notificationLink;
                                }
                                ?>
                                <a href="<?php echo BASE_URL; ?>/projects/index.php" class="btn btn-sm btn-primary">View</a>
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
            "autoWidth": false
        });
    });
</script>
<?php require_once './includes/footer.php'; ?>