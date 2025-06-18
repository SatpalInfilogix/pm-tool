<?php
ob_start();
$plugins = ['datepicker'];
require '../includes/header.php';
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}
$errorMessage = '';
if (isset($_POST['add_employee'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phoneno = $_POST['phoneno'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $jobtitle = $_POST['jobt'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $dob = date('Y-m-d', strtotime($_POST['dob']));
    $doj = date('Y-m-d', strtotime($_POST['doj']));
    $password = $_POST['password'];
    $assignedLeaderId = isset($_POST['assigned_leader_id']) && $_POST['assigned_leader_id'] !== '' ? $_POST['assigned_leader_id'] : 'NULL';
    $epass = md5($password);
    $query = "SELECT * FROM users WHERE email = '$email' OR phone_number = '$phoneno'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $errorMessage =  "This email or phone number already exists";
    } else {
        $insert = "INSERT INTO users 
(name, email, date_of_birth, date_of_joining, gender, phone_number, address, job_title, role, status, password_hash, assigned_leader_id) 
VALUES 
('$name', '$email', '$dob', '$doj', '$gender', '$phoneno', '$address', '$jobtitle', '$role', '$status', '$epass', $assignedLeaderId)";

        header('Location: ' . BASE_URL . '/employees/index.php');
        if (mysqli_query($conn, $insert)) {
        } else {
            $errorMessage = mysqli_error($conn);
        }
    }
}
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/test/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
};
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add Employee </h4>
            <a href="./index.php" class="btn btn-primary d-flex"><i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back</a>
        </div>
    </div>
</div>
<div class="card">
    <div class="text-danger">
        <?php
        if ($errorMessage) {
            echo $errorMessage;
        }
        ?>
    </div>
    <?php
    include './form.php';
    ?>
</div>
<?php require '../includes/footer.php' ?>