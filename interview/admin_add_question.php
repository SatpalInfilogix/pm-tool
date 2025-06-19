<?php
// admin_add_question.php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);

    if (!empty($question)) {
        $stmt = $conn->prepare("INSERT INTO questions (question) VALUES (?)");
        $stmt->bind_param("s", $question);
        $stmt->execute();
        $msg = "Question added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-4">
            <h3>Add New Test Question</h3>
            <?php if (!empty($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <textarea name="question" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Question</button>
            </form>
        </div>
    </div>
</body>

</html>