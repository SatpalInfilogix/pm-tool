<!-- <?php
// require_once '../includes/header.php';
// require_once '../includes/functions.php';
// require_once '../includes/db.php';
// $user_values = userProfile();

// if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
//     $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
//     $_SESSION['toast'] = "Access denied. Employees only.";
//     header("Location: " . $redirectUrl);
//     exit();
// }

// if (isset($_POST['submit_attendance'])) {
//     $id = intval($_POST['id']);
//     $employee_id = intval($_POST['employee_id']);
//     $date = $_POST['date'];
//     $note = $_POST['note'];
//     $in_time_raw = trim($_POST['in_time']);
//     $out_time_raw = trim($_POST['out_time']);

//     $in_time = ($in_time_raw === '') ? '00:00:00' : date('H:i:s', strtotime($in_time_raw));
//     $out_time = ($out_time_raw === '') ? '00:00:00' : date('H:i:s', strtotime($out_time_raw));

//     $updateQuery = "UPDATE attendance SET 
//                     note = ?, in_time = ?, out_time = ?, date = ? 
//                     WHERE id = ? AND employee_id = ?";
//     $stmt = $conn->prepare($updateQuery);
//     $stmt->bind_param("ssssii", $note, $in_time, $out_time, $date, $id, $employee_id);
//     if ($stmt->execute()) {
//         header("Location: index.php?message=updated");
//         exit();
//     } else {
//         echo "Failed to update attendance: " . $stmt->error;
//     }
// }

// if (isset($_GET['id'])) {
//     $id = intval($_GET['id']);
//     $sqlquery = "SELECT a.*, u.name AS employee_name FROM attendance a JOIN users u ON a.employee_id = u.id WHERE a.id = ?";
//     $stmt = $conn->prepare($sqlquery);
//     $stmt->bind_param("i", $id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//         $row = $result->fetch_assoc();
?>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Attendance</h4>
                    <a href="./index.php" class="btn btn-primary d-flex">
                        <i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back
                    </a>
                </div>
            </div>
        </div>

        <!-- <?php include './form.php'; ?> -->

<?php
    // } 

// require_once '../includes/footer.php';
// ?> -->