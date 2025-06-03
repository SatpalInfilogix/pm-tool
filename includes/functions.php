<?php
require_once __DIR__ . '/db.php';

function isAuth()
{
    return isset($_SESSION['userId']);
}

function userProfile()
{
    global $conn;
    if (isAuth()) {
        $userId = $_SESSION['userId'];
        $sql = "SELECT * FROM users WHERE id = '$userId'";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return null;
        }
    }
    return null; // add this in case isAuth() is false
}

function getSetting($key)
{
    global $conn;
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = '" . $key . "'");

    if ($result && $row = $result->fetch_assoc()) {
        return $row['setting_value'];
    } else {
        return null; // or a default value like ''
    }
}
