<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Merr vleren e fotos aktuale per te fshire nese perdoruesi zgjedh
$userQuery = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
$userQuery->execute([':id' => $user_id]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $profile_pic = null;


    if (isset($_POST['delete_photo_pic']) && $_POST['delete_photo_pic'] == '1') {
        if (!empty($user['profile_pic']) && file_exists($user['profile_pic']) && $user['profile_pic'] != 'uploads/member.png') {
            unlink($user['profile_pic']); // fshin file nga serveri
        }
        $profile_pic = 'uploads/member.png'; // vendos default foton
    }

    if (!empty($password)) {
        if (
            strlen($password) < 6 ||
            !preg_match('/[A-Z]/', $password) || 
            !preg_match('/[0-9]/', $password) ||   
            !preg_match('/[\W_]/', $password)  
        ) {
            $_SESSION['error_message'] =
                "⚠️ Fjalekalimi duhet te kete minimum 6 karaktere, nje shkronje te madhe, nje numer dhe nje simbol.";
            header("Location: update_profile.php");
            exit;
        }
    }

    // kjo sherben per ngarkim te fotos                   // kjo eshte nese ska ndodh ndoje gabim gjate ngarkimit
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $allowed_types = ['image/jpeg','image/png','image/gif','image/avif']; // formin nje list me tipin e fotove te lejuara per upload

        if (in_array($_FILES['profile_pic']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION); // e merr edhe extension e file psh .png , .jpg.

            $filename = 'img/profile_' . $user_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filename); // tmp_name perdoret per me rujt files ne php
            $profile_pic = $filename;
        }
        
    }

    $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
    $checkUsername->execute([':username' => $username,':id' => $user_id]);

    if ($checkUsername->rowCount() > 0) {
        $_SESSION['error_message'] = "Ky username ekziston tashme!";
        header("Location: profile.php");
        exit;
    }

    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $checkEmail->execute([':email' => $email,':id' => $user_id]);

    if ($checkEmail->rowCount() > 0) {
        $_SESSION['error_message'] = "Ky email eshte perdorur tashme!";
        header("Location: profile.php");
        exit;
    }

    $fields = "username = :username, email = :email";
    $params = [':username' => $username,':email' => $email,':id' => $user_id];

    if (!empty($password)) {
        $fields .= ", password = :password";
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($profile_pic) {
        $fields .= ", profile_pic = :profile_pic";
        $params[':profile_pic'] = $profile_pic;
    }
    
    if (empty($username) || empty($email)) {
        $_SESSION['error_message'] = "⚠️ Username dhe Email nuk mund te jene bosh.";
        header("Location: update_profile.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "⚠️ Email-i duhet te kete format te sakte.";
        header("Location: update_profile.php");
        exit;
    }

    $updaterQuery = $conn->prepare("UPDATE users SET $fields WHERE id = :id");
    $updaterQuery->execute($params);

    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    if ($profile_pic) $_SESSION['profile_pic'] = $profile_pic;

    $_SESSION['success_message'] = "✅ Profili u perditesua me sukses!";
    header("Location: profile.php");
    exit;

    
$success_message = $_SESSION['success_message'] ?? "";
unset($_SESSION['success_message']);
}

header("Location: profile.php");
exit;
?>
