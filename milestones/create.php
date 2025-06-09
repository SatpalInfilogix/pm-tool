<?php
ob_start();
require_once '../includes/header.php';
$plugins = ['datepicker', 'select2'];
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}

if (isset($_POST['add_milestone'])) {
    $project_id = $_POST['project_id'];
    $milestone_name = $_POST['milestone_name'];
    $description = isset($_POST['description']) ? strip_tags($_POST['description']) : '';
    $due_date_raw = $_POST['due_date'];
    $completed_date_raw = $_POST['completed_date'] ?? null;
    $completed_date = !empty($completed_date_raw) ? date('Y-m-d', strtotime($completed_date_raw)) : null;
    $amount = $_POST['amount'] ? $_POST['amount'] : NULL;
    $currency_code = $_POST['currency_code'];
    $status = $_POST['status'];

    $errorMessage = '';
    $due_date = !empty($due_date_raw) ? date('Y-m-d', strtotime($due_date_raw)) : null;

    if (empty($project_id) || empty($milestone_name) || !$due_date || empty($currency_code) || empty($status)) {
        $errorMessage = "Please fill all required fields including due date.";
    }

    if (empty($amount)) {
        $errorMessage = "Amount is required.";
    }

    if (empty($errorMessage)) {
        $insertQuery = "INSERT INTO project_milestones 
(project_id, milestone_name, due_date, completed_date, amount, currency_code, description, status) 
VALUES ('$project_id', '$milestone_name', '$due_date', '$completed_date', '$amount', '$currency_code', '$description', '$status')";



        // Insert milestone
        if (mysqli_query($conn, $insertQuery)) {
            $milestone_id = mysqli_insert_id($conn);

            if ($milestone_id > 0) {
                // Insert files if any
                if (!empty($_FILES['milestone_documents']['name'][0])) {
                    $uploadDir = "../uploads/milestones/";
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    foreach ($_FILES['milestone_documents']['name'] as $key => $filename) {
                        $tmpName = $_FILES['milestone_documents']['tmp_name'][$key];
                        $fileType = $_FILES['milestone_documents']['type'][$key];
                        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf', 'text/plain', 'video/mp4', 'video/avi', 'video/mov'];

                        if (!in_array($fileType, $allowedTypes)) {
                            continue;
                        }

                        $newFileName = time() . "-" . basename($filename);
                        $filePath = $uploadDir . $newFileName;

                        if (move_uploaded_file($tmpName, $filePath)) {
                            $fileInsertQuery = "INSERT INTO milestone_documents (milestone_id, file_path, file_name) 
                                        VALUES ('$milestone_id', '$filePath', '$newFileName')";
                            mysqli_query($conn, $fileInsertQuery);
                        }
                    }
                }

                // ✅ Redirect to index page with toast
                $_SESSION['toast'] = "Milestone created successfully!";
                header("Location: index.php");
                exit();
            } else {
                $errorMessage = "Failed to insert milestone, invalid milestone ID.";
            }
        } else {
            $errorMessage = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add New Milestone</h4>
            <a href="./index.php" class="btn btn-primary d-flex"><i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back</a>
        </div>
    </div>
</div>

<?php if (!empty($errorMessage)) : ?>
    <div class="alert alert-danger mx-3"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<div class="card">
    <?php include './form.php'; ?>
</div>

<?php require_once '../includes/footer.php'; ?>