<?php
ob_start();
require_once '../includes/header.php';

// Fetch milestone request (latest only)
$milestoneRequest = null;
$milestoneId = $_GET['id'] ?? 0;

$reqSql = "SELECT mr.*, u.name AS employee_name 
           FROM milestone_requests mr
           JOIN users u ON mr.employee_id = u.id
           WHERE mr.milestone_id = ?
           ORDER BY mr.created_at DESC
           LIMIT 1";

$reqStmt = $conn->prepare($reqSql);
$reqStmt->bind_param("i", $milestoneId);
$reqStmt->execute();
$reqResult = $reqStmt->get_result();

if ($reqResult && $reqResult->num_rows > 0) {
    $milestoneRequest = $reqResult->fetch_assoc();
}

$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin' && $user_values['role'] !== 'team leader')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}
$plugins = ['datepicker', 'select2'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $query = "SELECT * FROM project_milestones WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $docQuery = "SELECT * FROM milestone_documents WHERE milestone_id = '$id'";
        $docResult = mysqli_query($conn, $docQuery);
        $documents = mysqli_fetch_all($docResult, MYSQLI_ASSOC);
    } else {
        echo "<p class='text-danger'>Milestone not found!</p>";
        exit;
    }
} else {
    echo "<p class='text-danger'>Invalid request!</p>";
    exit;
}
$dueDateChangeNote = '';
if (!empty($milestoneRequest) && $milestoneRequest['status'] === 'approved' && $milestoneRequest['new_due_date'] === $row['due_date']) {
    $dueDateChangeNote = " (Updated via employee request)";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['milestone_request_id'], $_POST['decision'])) {
    require_once '../includes/db.php';

    $requestId = (int)$_POST['milestone_request_id'];
    $decision = $_POST['decision'] === 'approved' ? 'approved' : 'rejected';

    // Fetch request and employee info
    $reqSql = "SELECT mr.*, u.name AS employee_name, u.id AS employee_id, pm.milestone_name, p.name AS project_name
               FROM milestone_requests mr
               JOIN users u ON mr.employee_id = u.id
               JOIN project_milestones pm ON mr.milestone_id = pm.id
               JOIN projects p ON pm.project_id = p.id
               WHERE mr.id = ?";
    $stmt = $conn->prepare($reqSql);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        // Update milestone due_date if approved
        if ($decision === 'approved') {
            $updateMilestone = $conn->prepare("UPDATE project_milestones SET due_date = ? WHERE id = ?");
            $updateMilestone->bind_param("si", $request['new_due_date'], $request['milestone_id']);
            $updateMilestone->execute();
        }

        // Update request status
        $updateStatus = $conn->prepare("UPDATE milestone_requests SET status = ? WHERE id = ?");
        $updateStatus->bind_param("si", $decision, $requestId);
        $updateStatus->execute();

        // Notify employee
        $message = "Your milestone due date change request for '{$request['milestone_name']}' in project '{$request['project_name']}' has been {$decision}.";
        $link = "milestones/index.php";

        $notifySql = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
        $notifyStmt = $conn->prepare($notifySql);
        $notifyStmt->bind_param("iss", $request['employee_id'], $message, $link);
        $notifyStmt->execute();

        // Mark related admin notifications as read
        $milestoneId = $request['milestone_id'];
        $adminLink = "milestones/edit.php?id=" . $milestoneId;

        $markReadSql = "UPDATE notifications 
                SET read_status = 1 
                WHERE link = ? AND message LIKE ?";
        $likeMessage = "%milestone '{$request['milestone_name']}' in project%";

        $markStmt = $conn->prepare($markReadSql);
        $markStmt->bind_param("ss", $adminLink, $likeMessage);
        $markStmt->execute();


        $_SESSION['toast'] = "Request {$decision} successfully.";
    } else {
        $_SESSION['toast'] = "Request not found.";
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}


if (isset($_POST['edit-milestone'])) {
    $project_id = intval($_POST['project_id']);
    $milestone_name = mysqli_real_escape_string($conn, $_POST['milestone_name']);
    $description = isset($_POST['description']) ? strip_tags($_POST['description']) : '';
    $due_date = date('Y-m-d', strtotime($_POST['due_date']));
    $amount = !empty($_POST['amount']) ? floatval($_POST['amount']) : NULL;
    $currency_code = mysqli_real_escape_string($conn, $_POST['currency_code']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $completed_date = !empty($_POST['completed_date']) ? date('Y-m-d', strtotime($_POST['completed_date'])) : NULL;

    $projectCheckQuery = "SELECT id FROM projects WHERE id = '$project_id' AND type = 'fixed'";
    $result = mysqli_query($conn, $projectCheckQuery);

    if (mysqli_num_rows($result) > 0) {
        $updateQuery = "UPDATE project_milestones 
                        SET project_id='$project_id', milestone_name='$milestone_name', description='$description',
                            due_date='$due_date', amount=" . ($amount ? "'$amount'" : "NULL") . ", 
                            currency_code='$currency_code', status='$status', 
                            completed_date=" . ($completed_date ? "'$completed_date'" : "NULL") . "
                        WHERE id='$id'";

        if (mysqli_query($conn, $updateQuery)) {
            // Delete existing milestone documents before inserting new ones
            // Delete old files physically
            $oldFilesResult = mysqli_query($conn, "SELECT file_path FROM milestone_documents WHERE milestone_id = '$id'");
            while ($rowFile = mysqli_fetch_assoc($oldFilesResult)) {
                $filePath = realpath($rowFile['file_path']);
                if ($filePath && file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Now delete the DB rows
            mysqli_query($conn, "DELETE FROM milestone_documents WHERE milestone_id = '$id'");

            if (!empty($_FILES['milestone_documents']['name'][0])) {
                $uploadDir = "../uploads/milestones/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf', 'text/plain', 'video/mp4', 'video/avi', 'video/mov'];

                foreach ($_FILES['milestone_documents']['name'] as $key => $filename) {
                    $tmpName = $_FILES['milestone_documents']['tmp_name'][$key];
                    $fileType = $_FILES['milestone_documents']['type'][$key];

                    if (!in_array($fileType, $allowedTypes)) {
                        $errorMessage = "Invalid file type: $filename";
                        continue;
                    }

                    $newFileName = time() . "-" . basename($filename);
                    $filePath = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmpName, $filePath)) {
                        $fileInsertQuery = "INSERT INTO milestone_documents (milestone_id, file_path, file_name) 
                                    VALUES ('$id', '$filePath', '$newFileName')";

                        mysqli_query($conn, $fileInsertQuery);
                    }
                }
            }

            header('Location: ' . BASE_URL . '/milestones/index.php');
            exit();
        } else {
            $errorMessage = "Database Error: " . mysqli_error($conn);
        }
    } else {
        $errorMessage = "Invalid project selection. Please select a valid fixed project.";
    }
}
?>

<div class="row">
    <div class="col-12">

        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Edit Milestone</h4>
            <a href="./index.php" class="btn btn-primary d-flex"><i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back</a>
        </div>
    </div>
</div>
<?php if (!empty($milestoneRequest) && $milestoneRequest['status'] === 'pending' && ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr')) { ?>
    <div class="alert alert-info mt-3" role="alert">
        <h5 class="alert-heading"><i class="bx bx-time"></i> Milestone Change Request</h5>
        <hr>
        <div class="space pt-0 mt-0">
            <p><strong>Requested By:</strong> <?php echo htmlspecialchars($milestoneRequest['employee_name']); ?></p>
            <p><strong>Current Due Date:</strong> <?php echo htmlspecialchars($row['due_date']); ?></p>
            <p><strong>New Due Date:</strong> <?php echo htmlspecialchars($milestoneRequest['new_due_date']); ?></p>
            <p><strong>Reason:</strong><br><?php echo nl2br(htmlspecialchars($milestoneRequest['reason'])); ?></p>
            <p class="mb-0 text-muted"><small>Submitted on: <?php echo date('d M Y, h:i A', strtotime($milestoneRequest['created_at'])); ?></small></p>
        </div>

        <div class="mt-3 d-flex gap-2">
            <form method="post">
                <input type="hidden" name="milestone_request_id" value="<?= $milestoneRequest['id'] ?>">
                <input type="hidden" name="decision" value="approved">
                <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-check"></i> Accept</button>
            </form>

            <form method="post">
                <input type="hidden" name="milestone_request_id" value="<?= $milestoneRequest['id'] ?>">
                <input type="hidden" name="decision" value="rejected">
                <button type="submit" class="btn btn-danger btn-sm"><i class="bx bx-x"></i> Reject</button>
            </form>
        </div>
    </div>
<?php } ?>




<div class="card">
    <?php include './form.php'; ?>
</div>
<?php require_once '../includes/footer.php'; ?>