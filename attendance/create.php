<?php
ob_start();
require_once '../includes/header.php';
$user_values = userProfile();

if($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin'))
{
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl); 
    exit();
}
$userId = $_SESSION['userId'];
$errorMessage = '';
if (isset($_POST['add_attendance'])) {
    $employee_id = $_POST['employee'];
    $date = date('Y-m-d', strtotime($_POST['date']));
    $status = $_POST['status'];
    $note = $_POST['note'];
    $insert = "INSERT INTO attendance (employee_id, date, status, note) VALUES ('$employee_id', '$date', '$status', '$note')";
    if (mysqli_query($conn, $insert)) {
        header('Location: ' . BASE_URL . '/attendance/index.php');
        exit(); 
    } else {
        $errorMessage = "Error: " . mysqli_error($conn);
    }
}
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add Attendance</h4>
            <a href="./index.php" class="btn btn-primary d-flex"><i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back</a>
        </div>
    </div>
</div>
<?php
if ($errorMessage) {
    echo "<div class='alert alert-danger'>$errorMessage</div>";
}
?>
<?php include './form.php' ?>
<?php require_once '../includes/footer.php'; ?>
