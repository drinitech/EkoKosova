<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: Login.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: manage_reports.php");
    exit;
}

$id = intval($_GET['id']);
$error_message = "";

// Merr raportin ekzistues
$stmt = $conn->prepare("SELECT * FROM reports WHERE id=?");
$stmt->execute([$id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$report){
    die("Raporti nuk ekziston.");
}

// Merr profil-foton e user-it aktual
$profile_pic = 'uploads/member.png';
$stmtUser = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user_pic = $stmtUser->fetch(PDO::FETCH_ASSOC);
if($user_pic && !empty($user_pic['profile_pic'])){
    $profile_pic = htmlspecialchars($user_pic['profile_pic']);
}

// Ruaj input-et për rifreskim të form-it në rast gabimi
$old = [
    'name' => $report['name'],
    'email' => $report['email'],
    'city' => $report['city'],
    'type' => $report['type'],
    'description' => $report['description']
];

// Procesimi i form-it
if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $type = trim($_POST['type']);
    $description = trim($_POST['description']);
    $photo = $report['photo']; // foto ekzistuese

    // Rifreskoj vlerat e form-it
    $old = compact('name','email','city','type','description');

    // Validimet
    if(empty($name)) $error_message = "⚠️ Ju lutem shkruani emrin!";
    elseif(empty($email)) $error_message = "⚠️ Ju lutem shkruani email-in!";
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $error_message = "⚠️ Email-i nuk është valid!";
    elseif(empty($city)) $error_message = "⚠️ Ju lutem shkruani qytetin!";
    elseif(empty($type)) $error_message = "⚠️ Ju lutem shkruani llojin e raportimit!";
    elseif(empty($description)) $error_message = "⚠️ Ju lutem shkruani përshkrimin!";
    else {
        // Kontrollo nëse email-i i shkruar i përket një përdoruesi tjetër
        $checkUserEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkUserEmail->execute([$email, $_SESSION['user_id']]);
        if($checkUserEmail->rowCount() > 0){
            $error_message = "⚠️ Ky email i përket një përdoruesi tjetër në sistem!";
        }
    }

    // Ngarkimi i fotos së re
    if(empty($error_message) && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif', 'avif'];
        if(!in_array($ext, $allowed)){
            $error_message = "Format i pa lejuar për foton. Përdor jpg, jpeg, png ose gif.";
        } else {
            $filename = uniqid() . "." . $ext; // e krijon ni emer unik edhe i largohet overwrite
            $target = "uploads/" . $filename;
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $target)){
                // Fshi foton e vjetër
                if($photo && file_exists("uploads/".$photo)){
                    unlink("uploads/".$photo); // e fshin foton e vjeter nga serveri
                }
                $photo = $filename; // gati per ne databaze
            } else {
                $error_message = "Ndodhi një gabim gjatë ngarkimit të fotos.";
            }
        }
    }

    // Bëj update në DB nëse nuk ka gabime
    if(empty($error_message)){
        $update = $conn->prepare("
            UPDATE reports 
            SET name=?, email=?, city=?, type=?, description=?, photo=? 
            WHERE id=?
        ");
        $update->execute([$name, $email, $city, $type, $description, $photo, $id]);
        header("Location: manage_reports.php?updated=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User | EkoKosova</title>
<link rel="stylesheet" href="style.css">
<style>
.edit-container {
    max-width: 600px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
}

.edit-container h2 {
    color: #2e7d32;
    text-align: center;
    margin-bottom: 25px;
}

.edit-container label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
    color: #2e7d32;
}

.edit-container input[type=text],
.edit-container input[type=email],
.edit-container textarea,
.edit-container input[type=file] {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border: 1px solid #bfbfbf;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
}

.edit-container textarea {
    resize: vertical;
}

img.preview {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    margin-top: 10px;
}

input[type=submit] {
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

input[type=submit]:hover {
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

@media screen and (max-width: 992px) {
    .edit-container {
        max-width: 500px;
        margin: 40px auto;
        padding: 25px;
    }

    input[type=text],
    input[type=email],
    textarea,
    input[type=file] {
        font-size: 14px;
        padding: 10px;
        box-sizing: border-box;
    }

    input[type=submit] {
        font-size: 15px;
        padding: 10px;
        box-sizing: border-box;
    }

    img.preview {
        width: 90px;
        height: 90px;
    }
}


@media screen and (max-width: 768px) {
    .edit-container {
        margin: 20px;
        padding: 20px;
    }

    input[type=submit] {
        font-size: 15px;
        padding: 10px;
        box-sizing: border-box;
    }

    img.preview {
        width: 80px;
        height: 80px;
    }
}

/* Responsive per telefon */
@media screen and (max-width: 480px) {
    .edit-container {
        margin: 10px;
        padding: 15px;
    }

    input[type=text],
    input[type=email],
    textarea,
    input[type=file] {
        font-size: 14px;
        padding: 10px;
        box-sizing: border-box;
    }

    input[type=submit] {
        font-size: 14px;
        padding: 10px;
        
    }

    img.preview {
        width: 70px;
        height: 70px;
    }

    .back-link {
        font-size: 14px;
    }
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

        <a href="profile.php" class="profile-link">
            <img src="<?= $profile_pic ?>" alt="Profili Im" class="nav-profile-pic">
        </a>

        <?php if($_SESSION['is_admin'] == 1): ?>
            <a href="admin_dashboard.php" style="margin-left:10px;padding:10px 20px;background-color:green;color:white;text-decoration:none;border-radius:8px;">Dashboard</a>
        <?php endif; ?>

        <form action="Logout.php" method="POST" style="display:inline; margin-left:5px;">
            <button type="submit" class="translate"><img src="img/logout.png" class="logoutsymbol" style="width:20px;"></button>
        </form>

        <button class="translate" style="margin-left:5px;">🌐</button>
    </div>
</nav>
</header>

<main class="edit-container">
    <a href="manage_reports.php" class="back-link">⬅ Kthehu ne Dashboard</a>
    <h2>✏️ Edit Raportimin: <?= htmlspecialchars($report['name']) ?></h2>

    <?php if(!empty($error_message)): ?>
        <div class="error-message"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Emri:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($old['name']) ?>">

        <label>Email:</label>
        <input type="text" name="email" value="<?= htmlspecialchars($old['email']) ?>">

        <label>Qyteti:</label>
        <input type="text" name="city" value="<?= htmlspecialchars($old['city']) ?>">

        <label>Lloji:</label>
        <input type="text" name="type" value="<?= htmlspecialchars($old['type']) ?>">

        <label>Përshkrimi:</label>
        <textarea name="description" rows="5"><?= htmlspecialchars($old['description']) ?></textarea>

        <label>Foto ekzistuese:</label><br>
        <?php if($report['photo'] && file_exists("uploads/".$report['photo'])): ?>
            <img src="uploads/<?= htmlspecialchars($report['photo']) ?>" alt="foto raportit" class="preview"><br>
        <?php else: ?>
            <p>Nuk ka foto të ngarkuar</p>
        <?php endif; ?>

        <label>Ngarko foto te re (opsionale):</label>
        <input type="file" name="photo" accept="image/*">

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
