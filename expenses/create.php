<?php
ob_start();
require_once '../includes/header.php';
if (isset($_POST['add_expense'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $attachment = $_POST['attachment'];
    $status = $_POST['status'];
    $expense_date = date('Y-m-d', strtotime($_POST['expense_date']));
    $insertquery = "INSERT INTO expenses (title,amount,category_id,description,attachment,status,expense_date) 
    VALUES ('$title','$amount','$category_id','$description','$attachment','$status','$expense_date')";
    if (mysqli_query($conn, $insertquery)) {
        header('Location: ' . BASE_URL . './expenses/index.php');
    } else {
        $errorMessage = mysqli_error($conn);
    }
}
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box  pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add New Expense </h4>
            <a href="./index.php" class="btn btn-primary d-flex"><i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back</a>
        </div>
    </div>
</div>
<?php include './form.php' ?>
<?php require_once '../includes/footer.php'; ?>