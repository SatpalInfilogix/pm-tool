<?php
ob_start();
require_once './includes/header.php';
require_once './includes/db.php'; // ✅ fixed path if needed

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid question ID.");
}

$id = intval($_GET['id']);
$questionData = $conn->query("SELECT * FROM interview_questions WHERE id = $id")->fetch_assoc();

if (!$questionData) {
    die("Question not found.");
}

// Update question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedQuestion = trim($_POST['question']);

    if (!empty($updatedQuestion)) {
        $stmt = $conn->prepare("UPDATE interview_questions SET question = ? WHERE id = ?");
        $stmt->bind_param("si", $updatedQuestion, $id);
        $stmt->execute();
        header("Location: add-question.php?updated=1");
        exit;
    } else {
        $error = "Question cannot be empty.";
    }
}
?>


<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-4">Edit Interview Question</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <textarea name="question" class="form-control" rows="4" required><?= htmlspecialchars($questionData['question']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Update</button>
                <a href="add-question.php" class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>
<?php ob_end_flush(); ?>