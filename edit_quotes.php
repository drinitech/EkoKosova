<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: Login.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: konfigurimet.php");
    exit;
}

$profile_pic = 'uploads/member.png';

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=:id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_pic = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user_pic && $user_pic['profile_pic']){
        $profile_pic = htmlspecialchars($user_pic['profile_pic']);
    }
}
$id = intval($_GET['id']);

// Merr thënien ekzistuese
$stmt = $conn->prepare("SELECT * FROM quotes WHERE id=?");
$stmt->execute([$id]);
$quote = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$quote){
    echo "Thënia nuk ekziston.";
    exit;
}
if(isset($_POST['submit'])){
    $thenje_text = trim($_POST['thenje_text']);
    $autori = trim($_POST['autori']);
    $autor_img = $quote['autor_img'];
    $error_message = "";

    // Kontrollo nëse fusha e thënies është bosh
    if(empty($thenje_text) && empty($autori)){
        $error_message = "⚠️ Ju lutem shkruani thenien dhe emrin e autorit!";
    }
    // Vetëm thënia bosh
    elseif(empty($thenje_text)){
        $error_message = "⚠️ Ju lutem shkruani thenien!";
    }
    // Vetëm autori bosh
    elseif(empty($autori)){
        $error_message = "⚠️ Ju lutem shkruani emrin e autorit!";
    } 
    else {
        // Ngarkimi i fotos së re (opsionale)
        if(isset($_FILES['autor_img']) && $_FILES['autor_img']['error'] == 0){
            $ext = pathinfo($_FILES['autor_img']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . "." . $ext;
            $target = "uploads/" . $filename; // folderi ekziston

            if(move_uploaded_file($_FILES['autor_img']['tmp_name'], $target)){
                if($autor_img && file_exists($autor_img)){
                    unlink($autor_img);
                }
                $autor_img = $target;
            } else {
                $error_message = "⚠️ Ndodhi një gabim gjatë ngarkimit të fotos.";
            }
        }

        // Bëj update vetëm nëse nuk ka gabime
        if(empty($error_message)){
            $update = $conn->prepare("UPDATE quotes SET thenje_text=?, autori=?, autor_img=? WHERE id=?");
            $update->execute([$thenje_text, $autori, $autor_img, $id]);

            header("Location: konfigurimet.php?updated=1");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Quote | EkoKosova</title>
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
    <a href="konfigurimet.php" class="back-link">⬅ Kthehu te Lista e Thenieve</a>
    <h2>✏️ Edit Thenien</h2>

        <?php if(!empty($error_message)): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Thënie:</label>
        <textarea name="thenje_text" rows="4"><?= htmlspecialchars($quote['thenje_text']) ?></textarea>

        <label>Autor:</label>
        <input type="text" name="autori" value="<?= htmlspecialchars($quote['autori']) ?>">

        <label>Foto ekzistuese:</label><br>
        <?php if($quote['autor_img']): ?>
            <img src="<?= $quote['autor_img'] ?>" alt="foto autor" class="preview"><br>
        <?php else: ?>
            <p>Nuk ka foto të ngarkuar</p>
        <?php endif; ?>

        <label>Ngarko foto te re (opsionale):</label>
        <input type="file" name="autor_img" accept="image/*">

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
