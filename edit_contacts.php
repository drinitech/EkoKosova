<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: Login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_contacts.php");
    exit;
}

$id = (int) $_GET['id'];
$error_message = "";

$profile_pic = 'uploads/member.png';

$editContacts = $conn->prepare("SELECT * FROM contacts WHERE id = ?");
$editContacts->execute([$id]);
$contact = $editContacts->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=:id");
$stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$user_pic = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user_pic && $user_pic['profile_pic']){
        $profile_pic = htmlspecialchars($user_pic['profile_pic']);
    }

if (!$contact) {
    die("Kontakti nuk ekziston.");
}

if (isset($_POST['submit'])) {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $subject   = trim($_POST['subject']);
    $message   = trim($_POST['message']);

    if (empty($full_name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "⚠️ Ju lutem plotësoni të gjitha fushat!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "⚠️ Email-i nuk është valid!";
    } else {
        $update = $conn->prepare("
            UPDATE contacts 
            SET full_name=?, email=?, subject=?, message=? 
            WHERE id=?
        ");
        $update->execute([$full_name, $email, $subject, $message, $id]);

        header("Location: manage_contacts.php?updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<title>Edit Contact | Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
.edit-container {
    max-width: 600px;
    margin: 50px auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
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

.edit-container textarea,
.edit-container input[type=text] {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border: 1px solid #bfbfbf;
    border-radius: 8px;
    font-size: 15px;
}

.edit-container input[type=file] {
    margin-top: 10px;
}

img.preview{
    width:100px;
    height:100px;
    object-fit:cover;
    border-radius:50%;
    margin-top:10px;
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
    text-decoration: underline;
}

.error-message {
        max-width: 400px;
        margin: 15px auto;
        padding: 15px 20px;
        background-color: #f8d7da; 
        color: #842029;
        border-left: 5px solid #f5c2c7;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        animation: fadeIn 0.5s ease-in-out;
}


@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>
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
        <span class="welcome">
            <span style="color:white;">Miresevjen,</span>
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

<a href="manage_contacts.php" class="back-link">⬅ Kthehu te Kontaktet</a>
<h2>✏️ Edit Contact</h2>

<?php if ($error_message): ?>
    <div class="error-message"><?= $error_message ?></div>
<?php endif; ?>

<form method="POST">

    <label>Emri i plotë</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($contact['full_name']) ?>">

    <label>Email</label>
    <input type="text" name="email" value="<?= htmlspecialchars($contact['email']) ?>">

    <label>Subjekti</label>
    <input type="text" name="subject" value="<?= htmlspecialchars($contact['subject']) ?>">

    <label>Mesazhi</label>
    <textarea name="message" rows="5"><?= htmlspecialchars($contact['message']) ?></textarea>

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
                <li><a href="quotes.php">Thenje</a></li>
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
