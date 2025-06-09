<?php require_once './includes/header.php'; ?>
<?php
require_once './includes/db.php';


$userProfile = userProfile();



// Get notifications (do NOT mark them as read here)
function getNotifications($userProfile)
{
    global $conn;
    $notifications = [];

    if ($userProfile['role'] === 'employee') {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userProfile['id']);
    } else {
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

<!-- STYLING -->
<style>
    .unread-notification {
        background-color: #eaf3ff !important;
    }

    .unread-notification td:first-child {
        font-weight: 400;
        color:rgba(21, 21, 21, 0.79);
        font-size: 1rem;
    }

    .new-badge {
        background-color: #0d6efd;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 6px;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .btn-sm {
        padding: 2px 8px;
        font-size: 0.75rem;
    }
</style>

<!-- HEADER -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Notifications</h4>
            <span class="badge bg-info fs-6"><?php echo count($notifications); ?> Total</span>
        </div>
    </div>
</div>

<!-- NOTIFICATION TABLE -->
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
                        <tr class="<?php echo $noti['read_status'] == 0 ? 'unread-notification' : ''; ?>">
                            <td>
                                <?php echo htmlspecialchars($noti['message']); ?>
                                <?php if ($noti['read_status'] == 0): ?>
                                    <span class="new-badge">NEW</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($noti['created_at']); ?></td>
                            <td>
                                <?php
                                $link = $noti['link'] ?? '#';
                                if ($userProfile['role'] === 'employee' && strpos($noti['message'], 'assigned to a new project') !== false) {
                                    $link = BASE_URL . '/projects/index.php';
                                } else {
                                    if (strpos($link, 'http') === 0) {
                                        // full URL
                                    } elseif (strpos($link, '/') === 0) {
                                        $link = BASE_URL . $link;
                                    } else {
                                        $link = BASE_URL . '/' . $link;
                                    }
                                }
                                ?>
                                <?php if (!empty($link) && $link !== '#'): ?>
                                    <a href="<?php echo htmlspecialchars($link); ?>" class="btn btn-sm btn-outline-primary">View</a>
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

<!-- DATATABLE -->
<script>
    $(document).ready(function() {
        $('#notificationTable').DataTable({
            paging: true,
            ordering: true,
            info: true,
            lengthMenu: [10, 25, 50, 100],
            autoWidth: false,
            order: [
                [1, "desc"]
            ]
        });
    });
</script>

<?php require_once './includes/footer.php'; ?>