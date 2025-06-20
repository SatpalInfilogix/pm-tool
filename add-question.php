<?php
ob_start();
require_once './includes/header.php';
require_once './includes/db.php';
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin' && $user_values['role'] !== 'team leader')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}
// Initialize
$success = $error = "";
$editing = false;
$edit_id = null;
$edit_question = "";

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM interview_questions WHERE id = $id");
    header("Location: add-question.php");
    exit;
}

// Handle Edit Mode
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = true;
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM interview_questions WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_question = $result->fetch_assoc()['question'];
    } else {
        $error = "Question not found.";
    }
}

// Handle Form Submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    if (!empty($question)) {
        if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
            // Update
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE interview_questions SET question = ? WHERE id = ?");
            $stmt->bind_param("si", $question, $edit_id);
            $stmt->execute();
            $success = "Question updated successfully.";
            $editing = false;
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO interview_questions (question) VALUES (?)");
            $stmt->bind_param("s", $question);
            $stmt->execute();
            $success = "Question added successfully.";
        }
    } else {
        $error = "Please enter a question.";
    }
}
?>
<style>
    textarea{height: 10px;}
</style>
<div class="container-fluid py-4">
    <h2 class="mb-4"><?= $editing ? 'Edit' : 'Add' ?> Interview Question</h2>

    <div class="card">
        <div class="card-body">

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <textarea name="question" class="form-control" required placeholder="Write your interview question... "><?= htmlspecialchars($edit_question) ?></textarea>
                </div>
                <?php if ($editing): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                    <button type="submit" class="btn btn-primary">Update Question</button>
                    <a href="add-question.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                <?php endif; ?>
            </form>

            <hr class="my-4">

            <h4>All Questions</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM interview_questions ORDER BY id DESC");
                    $i = 1;
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['question']) ?></td>
                            <td>
                                <a href="add-question.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="add-question.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php require_once './includes/footer.php'; 
ob_end_flush();
?>