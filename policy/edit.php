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

if (isset($_POST['edit-policies'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    $uploadedFiles = [];
    if (isset($_POST['edit-policies'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];

        $uploadedFiles = [];

        if (!empty($_FILES['file']['name'][0])) {
            foreach ($_FILES['file']['name'] as $key => $filename) {
                $tmpName = $_FILES['file']['tmp_name'][$key];
                $relativePath = 'uploads/' . basename($filename);
                $absolutePath = '../' . $relativePath;

                if (move_uploaded_file($tmpName, $absolutePath)) {
                    $fullUrl = BASE_URL . '/' . $relativePath;
                    $uploadedFiles[] = $fullUrl;
                }
            }
        }

        // If files were uploaded, prepare the file string
        $fileString = !empty($uploadedFiles) ? implode(',', $uploadedFiles) : null;

        // Use conditional SQL depending on file update
        if ($fileString) {
            $sql = "UPDATE policies SET name = ?, description = ?, file = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssi", $name, $description, $fileString, $id);
                $stmt->execute();
            } else {
                die("Prepare failed: " . $conn->error);
            }
        } else {
            $sql = "UPDATE policies SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssi", $name, $description, $id);
                $stmt->execute();
            } else {
                die("Prepare failed: " . $conn->error);
            }
        }

        header('Location: ' . BASE_URL . '/policy/index.php');
        exit();
    }
}
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM policies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
?>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Policies</h4>
                    <a href="./index.php" class="btn btn-primary">Go back</a>
                </div>
            </div>
        </div>
        <div class="card">
            <?php include 'form.php'; ?>
        </div>
<?php
    } else {
        echo "<div class='alert alert-warning'>Policy not found.</div>";
    }
    $stmt->close();
}
require_once '../includes/footer.php';
ob_end_flush();
?>