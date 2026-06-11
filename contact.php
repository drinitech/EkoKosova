<?php
session_start();
include 'config.php';

$success = $_SESSION['success'] ?? "";
$error = $_SESSION['error'] ?? "";

// Ruaj input-et për të mbajtur vlerat
$old = $_SESSION['old'] ?? [];
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['old']);

$profile_pic = 'uploads/member.png'; 
$userEmail = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Merr foto dhe email nga DB
    $stmtUser = $conn->prepare("SELECT profile_pic, email FROM users WHERE id = :id");
    $stmtUser->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmtUser->execute();
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if (!empty($userData['profile_pic'])) {
            $profile_pic = htmlspecialchars($userData['profile_pic']);
        }
        $userEmail = trim($userData['email']);
    }
}

if (isset($_POST['send_contact'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Ruaj input-et për rifreskim
    $_SESSION['old'] = [
        'full_name' => $full_name,
        'subject' => $subject,
        'message' => $message
        // Note: Email nuk e ruajim sepse në gabim duhet të pastrohet
    ];

    if (empty($full_name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = "Ju lutem plotësoni të gjitha fushat.";
        header("Location: contact.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email-i nuk është valid.";
        header("Location: contact.php");
        exit;
    }

    // Kontrollo me email-in e profilit
    if ($email !== $userEmail) {
        $_SESSION['error'] = "Email-i nuk përputhet me email-in e profilit tuaj.";
        header("Location: contact.php");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO contacts (full_name, email, subject, message)
        VALUES (:full_name, :email, :subject, :message)
    ");

    $stmt->execute([
        ':full_name' => $full_name,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message
    ]);

    // Pas submit, fshij të gjitha input-et
    unset($_SESSION['old']);

    $_SESSION['success'] = "Mesazhi u dërgua me sukses!";
    header("Location: contact.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kontakti</title>
<link rel="stylesheet" href="style.css">
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
            <li><a href="contact.php" class="active">Kontakti</a></li>
            <li><a href="quotes.php">Thenie</a></li>
        </ul>

        <div class="nav-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="welcome">
                    <span style="color:white;">Miresevjen,</span>
                    <strong style="color:white;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                </span>

                <a href="profile.php" class="profile-link">
                    <img src="<?= $profile_pic ?>" alt="Profili Im" class="nav-profile-pic">
                </a>

                <?php if($_SESSION['is_admin'] == 1): ?>
                    <a href="admin_dashboard.php" style="margin-left:10px;padding:10px 20px;background-color:green;color:white;text-decoration:none;border-radius:8px;transition:0.3s;">Dashboard</a>
                <?php endif; ?>

                <form action="Logout.php" method="POST" class="translate" style="display:inline; margin-left:5px;">
                    <button type="submit" class="translate">
                        <img src="img/logout.png" class="logoutsymbol" style="width:20px;">
                    </button>
                </form>

                <button class="translate" style="margin-left:5px;">🌐</button>

            <?php else: ?>
                <button class="login">
                    <a href="Login.php" style="text-decoration:none;color:white;">Kyçu</a>
                </button>

                <button class="signup">
                    <a href="Signup.php" style="text-decoration:none;color:white;">Regjistrohu</a>
                </button>

                <button class="translate">🌐</button>
            <?php endif; ?>
        </div>
    </nav>
</header>

<section class="hero">
  <div>
    <h2>Na kontaktoni</h2>
    <p>Ne jemi këtu për t'ju ndihmuar dhe për t'iu përgjigjur çdo pyetjeje që keni.</p>
  </div>
</section>


<section class="report-section">
<h2>Na dërgoni një mesazh</h2>

<?php if($error): ?>
<div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form class="report-form" method="POST" action="contact.php">
    <label>Emri dhe Mbiemri</label>
    <input type="text" name="full_name" placeholder="Shkruani emrin tuaj"
           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">

    <label>Email</label>
    <input type="email" name="email" placeholder="Shkruani email-in tuaj"
           value="" >

    <label>Tema</label>
    <input type="text" name="subject" placeholder="Tema e mesazhit"
           value="<?= htmlspecialchars($old['subject'] ?? '') ?>">

    <label>Mesazhi</label>
    <textarea name="message" rows="5" placeholder="Shkruani mesazhin tuaj këtu..."><?= htmlspecialchars($old['message'] ?? '') ?></textarea>

    <button id="submit" type="submit" name="send_contact">Dërgo</button>
</form>

</section>

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

<script>
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.classList.add('hide-alert');
        setTimeout(() => alert.remove(), 500);
    });
}, 3000);
</script>
</body>
</html>