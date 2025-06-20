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
if (isset($_POST['add_holiday'])) {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $description = strip_tags($_POST['description']);
    $type = $_POST['type'];
    $recurring = isset($_POST['recurring']) ? 1 : 0;
    $query = "SELECT * FROM holidays WHERE name = '$name' OR date = '$date'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $errorMessage =  "This name or date already exists";
    } else {
        $insert = "INSERT INTO holidays (name,date,description,type,recurring) VALUES 
        ('$name','$date','$description','$type', '$recurring')";
        header('Location: ' . BASE_URL . '/holidays/index.php');
        if (mysqli_query($conn, $insert)) {
        } else {
            $errorMessage = mysqli_error($conn);
        }
    }
}
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add Holiday</h4>
            <a href="./index.php" class="btn btn-primary">Go back</a>
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