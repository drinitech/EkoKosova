<?php
session_start();
include 'config.php';

$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: admin_dashboard.php");
    exit;
}

if($id == $_SESSION['user_id']){
    header("Location: manage_users.php?error=self_delete");
    exit;
}

$userQuery = $conn->prepare("DELETE FROM users WHERE id = ?");
$userQuery->execute([$id]);

header("Location: manage_users.php?success=deleted");
exit;
?>