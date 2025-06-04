<?php
ob_start();
require_once '../includes/header.php';
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}
$userProfile = userProfile();
$userId = $userProfile['id'];
$userRole = $userProfile['role'];
if (isset($_POST['edit_leave'])) {
    $id = $_GET['id'] ?? 0;

    // Get existing leave info (to fetch employee_id and current status)
    $getEmp = mysqli_query($conn, "SELECT employee_id, status FROM leaves WHERE id = '$id'");
    if (!$getEmp || mysqli_num_rows($getEmp) == 0) {
        die("Leave not found.");
    }
    $leaveData = mysqli_fetch_assoc($getEmp);
    $employee_id = $leaveData['employee_id'];
    $oldStatus = $leaveData['status'];

    // Get new form data
    $leave_type = ucfirst(strtolower($_POST['leave_type']));
    $start_date = $_POST['start_date'];
    $end_date =  $_POST['end_date'];
    $status = ucfirst(strtolower($_POST['status']));
    $wasApprovedBefore = strtolower($oldStatus) === 'approved';
    $isNowApproved = strtolower($status) === 'approved';
    $isPaidLeave = strtolower($leave_type) === 'paid';
    $daysRequested = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
    if ($isNowApproved && !$wasApprovedBefore && $isPaidLeave) {
    // Store how many days were approved for audit/debug
    $logSql = "INSERT INTO leave_logs (employee_id, leave_id, days, action) VALUES ('$employee_id', '$id', '$daysRequested', 'approved')";
    mysqli_query($conn, $logSql);
}
    $reason = $_POST['reason'];

    // Update leave record
    $sql = "UPDATE leaves 
            SET leave_type = '$leave_type', start_date = '$start_date', end_date = '$end_date', status = '$status', reason = '$reason'
            WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);

    // Notify user if status changed
    if ($result && $status !== $oldStatus && in_array($status, ['approved', 'rejected'])) {
        $message = "Your leave request from $start_date to $end_date has been $status.";
        $link = BASE_URL . "/leaves/view.php?id=$id"; // make sure this link is valid in your project
        $notifSql = "INSERT INTO notifications (user_id, message, link) VALUES ('$employee_id', '$message', '$link')";
        mysqli_query($conn, $notifSql);
    }

    header('Location: ' . BASE_URL . '/leaves/index.php');
    exit();
}


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sqlquery = "SELECT * FROM leaves WHERE id={$id} ";
    $result = mysqli_query($conn, $sqlquery);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
?>
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Edit Leave </h4>
                        <a href="./index.php" class="btn btn-primary d-flex"><i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back</a>
                    </div>
                </div>
            </div>
            <div class="card">
                <?php
                include 'form.php';
                ?>
            </div>

<?php }
    }
} ?>
<?php require_once '../includes/footer.php'; ?>