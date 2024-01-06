<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php", true, 302);
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

if ($is_admin) {
    header("Location: user_view.php", true, 302);
    exit();
} else {
    header("Location: dashboard.php", true, 302);
    exit();
}
?>
