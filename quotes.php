<?php
session_start();
include 'config.php';

$error_message = ""; 
$success_message = "";

// Nese ki account mund te shtosh thenie perndryshe vetem admin dhe useri aktiv mund te shtojne
if(isset($_SESSION['user_id']) && isset($_POST['submit'])){
    $thenje_text = trim($_POST['thenje_text']); 
    $autori = trim($_POST['autori']);      

    if(empty($thenje_text) || empty($autori)){
        $error_message = "⚠️ Ju lutem plotësoni të gjitha fushat.";
    } elseif(!isset($_FILES['autor_img']) || $_FILES['autor_img']['error'] == 4){
        $error_message = "⚠️ Ju lutem vendosni një foto për autorin.";
    } else {
        $autor_img = null;
        if($_FILES['autor_img']['error'] == 0){
            $ext = pathinfo($_FILES['autor_img']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . "." . $ext;
            $target = "uploads/" . $filename;

            if(!is_dir('uploads')){
                mkdir('uploads', 0777, true);
            }

            if(move_uploaded_file($_FILES['autor_img']['tmp_name'], $target)){
                $autor_img = $target;
            } else {
                $error_message = "⚠️ Ndodhi një gabim gjatë ngarkimit të fotos.";
            }
        }

        if(empty($error_message)){
            $quotesQuery = $conn->prepare("INSERT INTO quotes (thenje_text, autori, autor_img) VALUES (?, ?, ?)");
            $quotesQuery->execute([$thenje_text, $autori, $autor_img]);

            $success_message = "✅ Thenia u shtua me sukses!";
        }
    }
}


$profile_pic = 'img/member.png'; 

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

$queryQuotes = $conn->prepare("SELECT thenje_text, autori, autor_img FROM quotes ORDER BY id DESC");
$queryQuotes->execute();
$quotes = $queryQuotes->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theniet</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>

html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    margin: 0;
}   

.quotes-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    padding: 0 10px;
    box-sizing: border-box;
}


/* Foto autorit responsive */
.quote-card img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}

.quote-card p {
    text-align: center;
    font-size: 14px;
    margin-bottom: 10px;
    word-wrap: break-word;   
    overflow: hidden;     
    display: -webkit-box;
    -webkit-line-clamp: 10;   /* trego vetëm 10 rreshta */
    -webkit-box-orient: vertical;
}

    
.form-quotes{ 
    width: 90%; /* zgjerohet në celular */
    max-width: 600px;
    margin:30px auto; 
    background:#fff; 
    padding:20px; 
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
.form-quotes input, form textarea{
     width:100%;
    padding:10px; 
    margin:5px 0 15px 0; 
    border:1px solid #ccc; 
    border-radius:5px;
}
.form-quotes button{ 
    background:#2E7D32; 
    color:#fff; 
    padding:10px 20px; 
    border:none; 
    border-radius:5px; 
    cursor:pointer;
    margin-left:38%;
}
.form-quotes button:hover{ 
    background:#1b5e20; 
}

.footer{
    margin-top:88px;
}

#success-popup {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
#success-popup .popup-content {
    width: 90%;
    max-width: 400px;
    background: #d1e7dd;
    color: #0f5132;
    padding: 25px 35px;
    border-radius: 10px;
    text-align: center;
    font-weight: bold;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
#success-popup .popup-content button {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #0f5132;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
#success-popup .popup-content button:hover {
    background-color: #0b3b26;
}

.error-message {
    background-color: #ffdddd;
    color: #d8000c;
    border-left: 5px solid #d8000c;
    padding: 12px 15px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
}

.manage-quote-btn {
    margin-top: auto;
    display: inline-block;
    padding: 6px 12px;
    background-color: #2E7D32;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-size: 13px;
    transition: 0.3s;
}

.manage-quote-btn:hover {
    background-color: #1b5e20;
}

.quotes-section h2{
        margin-left: 43%;
}

.page-content {
    flex: 1;
}


@media screen and (max-width:768px){
    .dashboard-container {
        flex-direction: column;
        align-items: center;
    }
    .dashboard-card {
        width: 90%;
    }
    .user-table {
        font-size: 14px;
    }
    .quotes-section h2{
        margin-left: 110px;
    }
}

@media screen and (min-width: 1280px) and (max-width: 1439px) {
.quotes-section h2{
        margin-left: 57%;
}
}


</style>
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
            <li><a href="quotes.php" class="active">Thenie</a></li>
        </ul>
        <div class="nav-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="welcome">
                    <span style="color:white;">Miresevjen,</span>
                    <strong style="color:white;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                </span>

                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="profile-link">
                        <img src="<?= $profile_pic ?>" alt="Profili Im" class="nav-profile-pic">
                    </a>
                <?php endif; ?>

                <?php if($_SESSION['is_admin'] == 1): ?>
                    <a href="admin_dashboard.php"style=" margin-left:10px;padding: 10px 20px;background-color: green;color: white;text-decoration: none;border-radius: 8px;transition: 0.3s;">Dashboard</a>
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


<section class="hero-quotes">
    <div>
        <h2 style="font-size: 45px;">Inspirim për Mbrojtjen e Mjedisit</h2>
        <h3>Fjalë që na kujtojnë rëndësinë e natyrës dhe përgjegjësinë tonë</h3>
    </div>
</section>

<section class="quotes-section">
    <h2>Thenie Mjedisore</h2>

    <div class="quotes-container">
        <?php if(!empty($quotes)): ?>
            <?php foreach($quotes as $q): ?>
               <div class="quote-card">
                    <img src="<?= $q['autor_img'] ? $q['autor_img'] : 'img/member.png' ?>" alt="autor">
                    <p>"<?= htmlspecialchars($q['thenje_text']) ?>"</p>
                    <span>- <?= htmlspecialchars($q['autori']) ?></span>

                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <a href="konfigurimet.php#quote-<?= $q['id'] ?>" class="manage-quote-btn">Menaxho Thënie</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; width:100%;">Nuk ka thënie të publikuara ende.</p>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="add-quote-form">
        <h3 style="text-align:center;">Shto Thenie te Re</h3>
        <form class="form-quotes" action="" method="POST" enctype="multipart/form-data">

            <?php if(!empty($error_message)): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>

            <label>Thenie:</label>
            <textarea name="thenje_text" rows="4"><?= isset($_POST['thenje_text']) ? htmlspecialchars($_POST['thenje_text']) : '' ?></textarea>

            <label>Autor:</label>
            <input type="text" name="autori" value="<?= isset($_POST['autori']) ? htmlspecialchars($_POST['autori']) : '' ?>">

            <label>Foto Autor:</label>
            <input type="file" name="autor_img" accept="image/*">

            <button type="submit" name="submit">Shto Thenie</button>
        </form>
    </div>
    <?php endif; ?>
</section>

<?php if(!empty($success_message)): ?>
<div id="success-popup">
    <div class="popup-content">
        <?= $success_message ?>
        <br>
        <button id="popup-ok">OK</button>
    </div>
</div>

<script>
const popup = document.getElementById('success-popup');
popup.style.display = 'flex';

// Disable form inputs while popup is visible
const inputs = document.querySelectorAll('.form-quotes input, .form-quotes textarea, .form-quotes button');
inputs.forEach(input => input.disabled = true);
inputs.forEach(input => input.style.backgroundColor = "#e9ecef");
inputs.forEach(input => input.style.cursor = "not-allowed");

document.getElementById('popup-ok').addEventListener('click', function(){
    popup.style.display = 'none';
    // Optionally rifresko faqen për të pastruar formën
    window.location.href = "quotes.php";
});
</script>
<?php endif; ?>
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



