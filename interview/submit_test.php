<?php
session_start();
require_once '../includes/db.php'; // adjust if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_SESSION['test_name'] ?? null;
    $email = $_SESSION['test_email'] ?? null;
    $phone = $_SESSION['test_phone'] ?? null;

    // Fetch all questions
    $questions = [];
    $result = $conn->query("SELECT id, question FROM interview_questions ORDER BY id ASC");
    while ($row = $result->fetch_assoc()) {
        $questions[$row['id']] = $row['question'];
    }

    $answersJson = [];

    foreach ($questions as $id => $question) {
        $fieldName = 'q' . $id;
        $answer = trim($_POST[$fieldName] ?? '');
        $answersJson["q$id"] = $answer;
    }

    $formattedAnswers = json_encode($answersJson);

    // ✅ Save test submission
    $stmt = $conn->prepare("INSERT INTO test_submissions (name, email, phone, answers, submitted_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $phone, $formattedAnswers);
    $stmt->execute();
    $stmt->close();

    // ✅ Notify all admins
    $notificationMessage = "$name has submitted a test.";
    $link = 'submitted-answers.php'; // or include an ID if needed, like 'view_submission.php?id=123'
    $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin'");
    while ($admin = $adminQuery->fetch_assoc()) {
        $adminId = $admin['id'];
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $adminId, $notificationMessage, $link);

        $stmt->execute();
        $stmt->close();
    }

    // ✅ Clear session
// ✅ Clear session and redirect to thank-you page
session_destroy();
header("Location: thankyou.php");
exit;
}
?>

