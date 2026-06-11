<?php
session_start();
include_once('config.php');

if(isset($_POST['submit'])){
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)){
        echo "<script>alert('Plotësoni të gjitha fushat.'); window.history.back();</script>";
        exit;
    }

    $sql = "SELECT id, name, username, email, password, is_admin FROM users WHERE username = :username";
    $userQuery = $conn->prepare($sql);
    $userQuery->bindParam(':username', $username);
    $userQuery->execute();

    $user = $userQuery->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        echo "<script>alert('Username nuk ekziston.'); window.history.back();</script>";
        exit;
    }


    if(password_verify($password, $user['password'])){

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
    
        if($user['is_admin'] == 1){
            header("Location: admin_dashboard.php"); 
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    
    } else {
        echo "<script>alert('Fjalëkalimi është i gabuar.'); window.history.back();</script>";
        exit;
    }
    
}
?>
