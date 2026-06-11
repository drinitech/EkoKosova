<?php
session_start();

$success_message = $_SESSION['success_message'] ?? "";
$error_message = $_SESSION['error_message'] ?? "";
$old = $_SESSION['old'] ?? [];

unset($_SESSION['old']);
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Krijo Llogari</title>

<style>
.error-alert {
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
        
}

.success-alert {
    max-width: 400px;
    margin: 15px auto;
    padding: 15px 20px;
    background-color: #d1e7dd; 
    color: #0f5132;
    border-left: 5px solid #badbcc;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);

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
                <button class="login"><a href="Login.php" style="text-decoration: none; color: white;">Kyçu</a></button>
                <button class="signup"><a href="Signup.php" style="text-decoration: none; color: white;">Regjistrohu</a></button>
                <button class="translate">🌐</button>
            </div>
        </nav>
    </header>

    <div class="signup-container">
        <div class="signup-card">
            <h2>Krijo llogari</h2>

            <?php if(!empty($error_message)): ?>
                <div class="error-alert"><?= $error_message ?></div>
            <?php endif; ?>
            
            <form action="SignUpLogic.php" method="post" enctype="multipart/form-data">
                <label>Emri dhe Mbiemri</label>
                <input type="text" name="name" placeholder="Shkruani Emrin dhe Mbiemrin" value="<?= htmlspecialchars($old['name'] ?? '') ?>">

                <label>Email</label>
                <input type="text" name="email" placeholder="Shkruani emailin tuaj" value="<?= htmlspecialchars($old['email'] ?? '') ?>">


                <label>Username</label>
                <input type="text" name="username" placeholder="Shkruani username tuaj" value="<?= htmlspecialchars($old['username'] ?? '') ?>">


                <label>Fjalëkalimi</label>
                <input type="password" name="password" placeholder="Krijo një fjalëkalim">

                <label>Konfirmo fjalëkalimin</label>
                <input type="password" name="confirm_password" placeholder="Konfirmo fjalëkalimin">

                <label>Ngarko foto profili:</label>
                <input type="file" name="profile_pic" accept="image/*">

                <button class="signup-btn" type="submit" name="submit">Regjistrohu</button>
            </form>

            <a href="Login.php">Keni tashmë një llogari? Kyçu</a>
        </div>
    </div>
    <?php if(!empty($success_message)): ?>
<style>
#popup-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#popup-box {
    background: #d1e7dd;
    color: #0f5132;
    padding: 25px 35px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    text-align: center;
    max-width: 400px;
    font-weight: bold;
}

#popup-box button {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #0f5132;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
#popup-box button:hover {
    background-color: #0b3b26;
}
</style>

<div id="popup-backdrop">
    <div id="popup-box">
        <?= $success_message ?>
        <br>
        <button id="popup-ok">OK</button>
    </div>
</div>

<script>

const inputs = document.querySelectorAll('.signup-card input');
const submitBtn = document.querySelector('.signup-card .signup-btn');
inputs.forEach(input => input.disabled = true);
submitBtn.disabled = true;
submitBtn.style.cursor = "not-allowed";
inputs.forEach(input => input.style.backgroundColor = "#e9ecef");
submitBtn.style.backgroundColor = "#6c757d";

document.getElementById('popup-ok').addEventListener('click', function(){
    window.location.href = "Login.php";
});
</script>
<?php endif; ?>

</body>
</html>
