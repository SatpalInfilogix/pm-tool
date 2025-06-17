<?php
ob_start();
require_once './includes/db.php';
require_once './includes/header.php'; // For userProfile function

$userProfile = userProfile();

if (!isset($_GET['noti_id'])) {
    // no notification id? redirect to notification list
    header('Location: ' . BASE_URL . '/notifications.php');
    exit;
}

$noti_id = intval($_GET['noti_id']);

// Fetch notification info
$stmt = $conn->prepare("SELECT * FROM notifications WHERE id = ?");
$stmt->bind_param("i", $noti_id);
$stmt->execute();
$result = $stmt->get_result();
$notification = $result->fetch_assoc();

if (!$notification) {
    // notification not found
    header('Location: ' . BASE_URL . '/notifications.php');
    exit;
}

// Security check: If employee, only allow their own notifications
if ($userProfile['role'] === 'employee' && $notification['user_id'] != $userProfile['id']) {
    header('Location: ' . BASE_URL . '/notifications.php');
    exit;
}

// Mark notification as read
$stmtMarkRead = $conn->prepare("UPDATE notifications SET read_status = 1 WHERE id = ?");
$stmtMarkRead->bind_param("i", $noti_id);
$stmtMarkRead->execute();

// Redirect to the actual link
$link = $notification['link'] ?? '#';

if ($userProfile['role'] === 'employee') {
    if (strpos($notification['message'], 'assigned to a new project') !== false) {
        $link = BASE_URL . '/projects/index.php';
    } elseif (strpos($notification['message'], 'milestone due date change request') !== false) {
        $link = BASE_URL . '/milestones/index.php';
    }
} else {
    if (strpos($link, 'http') === 0) {
        // full URL, do nothing
    } elseif (strpos($link, '/') === 0) {
        $link = BASE_URL . $link;
    } else {
        $link = BASE_URL . '/' . $link;
    }
}

header("Location: " . $link);
exit;
ob_end_flush();
