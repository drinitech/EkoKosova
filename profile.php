<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$profile_pic = 'uploads/member.png';

$userQuery = $conn->prepare("SELECT id, name, username, email, profile_pic FROM users WHERE id = :id");
$userQuery->bindParam(':id', $user_id, PDO::PARAM_INT);
$userQuery->execute();
$user = $userQuery->fetch(PDO::FETCH_ASSOC); // i merr si array asociative ne te cilen ka keys dhe values

$error_message = $_SESSION['error_message'] ?? ''; // e merr vleren ose mbetet bosh
$success_message = $_SESSION['success_message'] ?? '';

unset($_SESSION['error_message'], $_SESSION['success_message']); // kur tbohet refresh faqja nuk shfaqet prap error message ose success message sepse perdoren vetem nje here ne sesion



if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    $queryPic = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
    $queryPic->bindParam(':id', $user_id, PDO::PARAM_INT);
    $queryPic->execute();
    $user_pic = $queryPic->fetch(PDO::FETCH_ASSOC);

    if($user_pic && $user_pic['profile_pic']){
        $profile_pic = htmlspecialchars($user_pic['profile_pic']);
    }
}

?>
<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profili im | EkoKosova</title>
<link rel="stylesheet" href="style.css">
<style>

html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    margin: 0;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-left:0px;
    justify-content: center;
    text-align: center;
    flex-wrap: wrap;
}


.profile-container {
    width: 95%;
    max-width: 900px;
    margin: 40px auto;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
}


.profile-header img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #2e7d32;
}

.profile-header div{
    width: 100%;
}

.profile-header div h2 {
    margin: 0;
    color: #2e7d32;
}

.profile-header div p {
    margin: 5px 0 0 0;
    color: #555;
}

.profile-section {
    margin-top: 30px;
}

.profile-section h3 {
    color: #2e7d32;
    margin-bottom: 15px;
}

.settings-form input[type=text],
.settings-form input[type=password],
.settings-form input[type=file] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #bfbfbf;
}

.settings-form input[type=submit] {
    padding: 12px;
    background: #2e7d32;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    width: 100%;
}

.settings-form input[type=submit]:hover {
    background: #1b5e20;
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

.popup-overlay {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.5);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.popup-box {
    background: #4CAF50;
    color: white;
    padding: 25px 40px;
    border-radius: 12px;
    font-size: 18px;
    text-align: center;
    position: relative;
    min-width: 300px;
}

.popup-box.error {
    background: #f44336;
}

.popup-box span.close {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    font-weight: bold;
}


.page-content {
    flex: 1;
}

@media (min-width: 769px) and (max-width: 1024px) {

    .profile-container {
        width: 90%;
        padding: 25px;
    }

    .profile-header {
        gap: 30px;
    }

    .profile-header img {
        width: 110px;
        height: 110px;
    }

    .profile-header h2 {
        font-size: 22px;
    }

    .profile-header p {
        font-size: 15px;
    }

    .settings-form input[type=text],
    .settings-form input[type=password],
    .settings-form input[type=file] {
        font-size: 15px;
    }

    .nav-profile-pic {
        width: 45px;
        height: 45px;
    }
}

@media (max-width: 480px) {

    .profile-container {
        padding: 15px;
    }

    .profile-header img {
        width: 90px;
        height: 90px;
    }

    .settings-form input[type=text],
    .settings-form input[type=password],
    .settings-form input[type=file] {
        font-size: 14px;
    }

    .popup-box {
        width: 90%;
        font-size: 16px;
    }
}
</style>
</head>
<body>
<main class="page-content">
<header>
    <nav class="navbar">
        <div class="logo">🌿 EkoKosova</div>
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon">&#9776;</label>
        <ul class="nav-links">
            <li><a href="index.php">Ballina</a></li>
            <li><a href="about.php">Rreth Nesh</a></li>
            <li><a href="Reports.php">Raportimet</a></li>
            <li><a href="contact.php">Kontakti</a></li>
            <li><a href="quotes.php">Thenie</a></li>
        </ul>
        <div class="nav-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="welcome" style="color:white;">Miresevjen, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="profile-link">
                        <img src="<?= $profile_pic ?>" alt="Profili Im" class="nav-profile-pic">
                    </a>
                <?php endif; ?>

                <?php if($_SESSION['is_admin'] == 1): ?>
                    <a href="admin_dashboard.php" style="margin-left:10px;padding:10px 20px;background-color:green;color:white;border-radius:8px; text-decoration:none;">Dashboard</a>
                <?php endif; ?>

                <form action="Logout.php" method="POST" class="translate" style="display:inline; margin-left:5px;">
                    <button type="submit" class="translate">
                        <img src="img/logout.png" class="logoutsymbol" style="width:20px;">
                    </button>
                </form>
    
            <?php else: ?>
                <button class="login"><a href="Login.php" style="color:white;">Kyçu</a></button>
                <button class="signup"><a href="Signup.php" style="color:white;">Regjistrohu</a></button>
            <?php endif; ?>
            <button class="translate">🌐</button>
        </div>
    </nav>
</header>

<div class="profile-container">
    <a href="index.php" class="back-link">⬅ Kthehu prapa</a>

    <div class="profile-header">
        <img src="<?= $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'uploads/member.png' ?>" alt="Foto Profili">
        <div>
            <!-- per mbrojtjen e kodit nga hackeret parandalim te XSS attack(Cross Site Scripting) -->
            <h2><?= htmlspecialchars($user['name']) ?></h2>  
            <p>Username: <?= htmlspecialchars($user['username']) ?></p>
            <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <div class="profile-section">
        <h3>Settings Personale</h3>
       <form method="POST" action="update_profile.php" class="settings-form" enctype="multipart/form-data">
    <label>Ndrysho Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>">

    <label>Ndrysho Email</label>
    <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>">

    <label>Ndrysho Foto Profili (Opsionale)</label>
    <input type="file" name="profile_pic">

    <label>
        <input type="checkbox" name="delete_photo_pic" value="1"> Fshi foton aktuale
    </label><br><br>

    <label>Ndrysho Fjalekalimin (Opsionale)</label>
    <input type="password" name="password" placeholder="Fjalekalim i ri">

    <input type="submit" name="submit" value="Ruaj Ndryshimet">
</form>

    </div>
</div>
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
<!-- Kontrollon kodin e html qe me u shfaq vetem kur ka mesazhin e suksesit -->
<?php if($success_message): ?> 
<div class="popup-overlay">
    <div class="popup-box">
        <?= htmlspecialchars($success_message) ?>
        <span class="close">✖</span>
    </div>
</div>
<?php endif; ?>

<!-- Kontrollon kodin e html qe me u shfaq vetem kur ka mesazhin e suksesit -->
<?php if($error_message): ?>
<div class="popup-overlay">
    <div class="popup-box error">
        <?= htmlspecialchars($error_message) ?>
        <span class="close">✖</span>
    </div>
</div>
<?php endif; ?>

<script>
    // i zgjedh krejt elementet qe kan klasen close ne popup-box eshte butoni X 
document.querySelectorAll('.popup-box .close').forEach(btn => {
    btn.addEventListener('click', () => { // kur perdoruesi e klikon butonin te ekzekutohet funksioni brenda
        btn.parentElement.parentElement.style.display = 'none'; // mbyllet popup
    });
});

setTimeout(() => {
    document.querySelectorAll('.popup-overlay').forEach(p => p.style.display='none'); // per 6 sekonda mbyllet popup
}, 6000);
</script>

</body>
</html>
