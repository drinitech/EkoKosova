<?php
session_start();
include 'config.php';

// Vetëm admin mund të editojë
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: Login.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: admin_dashboard.php");
    exit;
}

$profile_pic = 'uploads/member.png'; 

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_pic = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user_pic && $user_pic['profile_pic']){
        $profile_pic = htmlspecialchars($user_pic['profile_pic']);
    }
}

$id = $_GET['id'];

$editQuery = $conn->prepare("SELECT id, name, username, email, is_admin FROM users WHERE id = :id");
$editQuery->bindParam(':id', $id);
$editQuery->execute();
$user = $editQuery->fetch(PDO::FETCH_ASSOC);

if(!$user){
    echo "Useri nuk ekziston.";
    exit;
}

$alert_msg = '';

if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Kontrollo nese username ekziston tek userat tjere
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
    $checkUser->execute([':username'=>$username, ':id'=>$id]);
    if($checkUser->rowCount() > 0){
        $alert_msg = "Ky username është përdorur më parë!";
    }

    // Kontrollo nese email ekziston tek userat tjere
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $checkEmail->execute([':email'=>$email, ':id'=>$id]);
    if($checkEmail->rowCount() > 0){
        $alert_msg = "Ky email është përdorur më parë!";
    }

    // Nese nuk ka gabime, update
    if(empty($alert_msg)){
        $userQuery = $conn->prepare("UPDATE users SET name=:name, username=:username, email=:email, is_admin=:is_admin WHERE id=:id");
        $userQuery->bindParam(':name', $name);
        $userQuery->bindParam(':username', $username);
        $userQuery->bindParam(':email', $email);
        $userQuery->bindParam(':is_admin', $is_admin);
        $userQuery->bindParam(':id', $id);
        $userQuery->execute();

        header("Location: manage_users.php?updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User | EkoKosova</title>
<link rel="stylesheet" href="style.css">
<style>
.edit-container {
    max-width: 500px;
    margin: 50px auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-align: left;
    font-family: Arial, sans-serif;
}

.edit-container h2 {
    color: #2e7d32;
    margin-bottom: 25px;
    text-align: center;
}

.edit-container label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
    color: #2e7d32;
}

.edit-container input[type=text],
.edit-container input[type=email] {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border: 1px solid #bfbfbf;
    border-radius: 8px;
    font-size: 15px;
}

.edit-container input[type=checkbox] {
    margin-top: 15px;
    transform: scale(1.2);
}

.edit-container input[type=submit] {
    margin-top: 25px;
    width: 100%;
    padding: 12px;
    background-color: #2e7d32;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.edit-container input[type=submit]:hover {
    background-color: #1b5e20;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #2e7d32;
    text-decoration: none;
    font-weight: bold;
}
.back-link:hover {
    text-decoration: none;
}

/* Alert */
.alert {
    background-color: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
    font-weight: bold;
}
</style>
</head>
<body><header>
<nav class="navbar">
    <div class="logo">🌿 EkoKosova</div>

    <input type="checkbox" id="menu-toggle">
    <label for="menu-toggle" class="menu-icon">&#9776;</label>

    <ul class="nav-links">
        <li><a href="index.php">Ballina</a></li>
        <li><a href="about.php">Rreth Nesh</a></li>
        <li><a href="Reports.php">Raportimet</a></li>
        <li><a href="contact.php">Kontakti</a></li>
        <li><a href="quotes.php">Thenje</a></li>
    </ul>

    <div class="nav-buttons">
        <span class="welcome">
            <span style="color:white;">Mirësevjen,</span>
            <strong style="color:white;"><?= htmlspecialchars($_SESSION['username']) ?></strong>
        </span>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="profile-link">
                <img src="<?= $profile_pic ?>" alt="Profili Im" class="nav-profile-pic">
            </a>
        <?php endif; ?>

        <?php if($_SESSION['is_admin'] == 1): ?>
            <a href="admin_dashboard.php" style="margin-left:10px;padding:10px 20px;background-color:green;color:white;text-decoration:none;border-radius:8px;transition:0.3s;">Dashboard</a>
        <?php endif; ?>

        <form action="Logout.php" method="POST" style="display:inline; margin-left:5px;">
            <button type="submit" class="translate">
                <img src="img/logout.png" class="logoutsymbol" style="width:20px;">
            </button>
        </form>

        <button class="translate" style="margin-left:5px;">🌐</button>
    </div>
</nav>
</header>

<main class="edit-container">
    <a href="manage_users.php" class="back-link">⬅ Kthehu ne Dashboard</a>
    <h2>✏️ Edit User: <?= htmlspecialchars($user['username']) ?></h2>

    <?php if($alert_msg): ?>
        <div class="alert"><?= $alert_msg ?></div>
        <script>
            setTimeout(() => {
                document.querySelector('.alert').remove();
            }, 6000);
        </script>
    <?php endif; ?>

    <form method="POST">
        <label>Emri:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>">

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

        <label>
            <input type="checkbox" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>> Admin
        </label>

        <input type="submit" name="submit" value="Ruaj Ndryshimet">
    </form>
</main>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-about">
            <h3 class="logo">🌿 EkoKosova</h3>
            <p>“Mbrojmë Natyrën, Përmirësojmë Kosovën”</p>
        </div>
        <div class="footer-links">
            <h4>Navigimi</h4>
            <ul>
                <li><a href="index.php">Ballina</a></li>
                <li><a href="about.php">Rreth Nesh</a></li>
                <li><a href="Reports.php">Raportimet</a></li>
                <li><a href="contact.php">Kontakti</a></li>
                <li><a href="quotes.php">Thenie</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Kontakti</h4>
            <p>Email: info@ekokosova.com</p>
            <p>Tel: +383 44 123 456</p>
            <p>Prishtinë, Kosovë</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 EkoKosova. Të gjitha të drejtat e rezervuara.</p>
    </div>
</footer>

</body>
</html>
