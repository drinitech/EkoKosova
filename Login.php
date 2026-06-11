<?php
session_start();
include_once('config.php');



$error_message = ""; 

if(isset($_POST['submit'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)){
        $error_message = "⚠️ Ju lutem plotesoni të gjitha fushat.";
    } else {
 
        $sql = "SELECT id, name, username, email, password, is_admin FROM users WHERE username = :username";
        $userQuery = $conn->prepare($sql);
        $userQuery->bindParam(':username', $username);
        $userQuery->execute();

        $user = $userQuery->fetch(PDO::FETCH_ASSOC);

        if(!$user){
            $error_message = "⚠️ Username nuk ekziston. Per shfrytezim te ketij aplikacioni duhet te krijoni nje llogari.";
        } else if(!password_verify($password, $user['password'])){
            $error_message = "⚠️ Fjalekalimi është i gabuar.";
        } else {
           
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
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Kyçu</title>

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
            <button class="login"><a href="Login.php" style="text-decoration: none;  color: white;">Kyçu</a></button>
            <button class="signup"><a href="Signup.php" style="text-decoration: none;  color: white;">Regjistrohu</a></button>
            <button class="translate">🌐</button>
         </div>
    </nav>
</header>

<div class="login-container">
    <div class="login-card">
        <h2>Kyçu</h2>

       <?php if($error_message != ""): ?>
            <div class="error-alert">
                <p><?= $error_message ?></p>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Username</label>
            <input type="text" placeholder="Shkruani username tuaj" name="username">

            <label>Fjalëkalimi</label>
            <input type="password" placeholder="Shkruani fjalëkalimin tuaj" name="password">

            <button class="login-btn" type="submit" name="submit">Kyçu</button>
        </form>

        <?php session_start(); ?>
        <a href="forgot_password.php?from=login">Keni harruar fjalekalimin tuaj?</a>
        <a href="Signup.php">Regjistrohu</a>
    </div>
</div>


<?php if(!empty($_SESSION['success_message'])): ?>
<style>
#popup-backdrop{
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#popup-box{
    background: #d1e7dd;
    color: #0f5132;
    padding: 25px 35px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    text-align: center;
    max-width: 400px;
    font-weight: bold;
}

#popup-box button{
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #0f5132;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
#popup-box button:hover{ 
    background-color: #0b3b26; 
}
</style>

<div id="popup-backdrop">
    <div id="popup-box">
        <?= $_SESSION['success_message'] ?>
        <br>
        <button id="popup-ok">OK</button>
    </div>
</div>

<script>
document.getElementById('popup-ok').addEventListener('click', function(){
    document.getElementById('popup-backdrop').style.display = 'none';
});

<?php unset($_SESSION['success_message']); ?>
</script>
<?php endif; ?>



    
</body>
</html>