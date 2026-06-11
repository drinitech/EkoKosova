<?php
session_start();
include 'config.php';

$error = "";

if(isset($_POST['submit'])){
    $email = trim($_POST['email']);

    if(empty($email)){
        $error = "Ju lutem shkruani email-in tuaj!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Ju lutem shkruani nje email valid!";
    } else {
        $userQuery = $conn->prepare("SELECT id FROM users WHERE email=?"); // gjeje userin me kete email
        $userQuery->execute([$email]);
        $user = $userQuery->fetch(PDO::FETCH_ASSOC); // merr te dhenat e userit si array asociativ

        if($user){
            $token = bin2hex(random_bytes(32)); // krijon nje token te sigurt me 32 karaktere
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // caktohet koha e skadimit te tokenit ne 1 ore nga momenti i krijimit

            $update = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?"); // tokenin edhe oren e skadimit e ruan ne tabelen users ne DB
            $update->execute([$token, $expires, $user['id']]);

            // Shkoni ne reset_password.php me token
            header("Location: reset_password.php?token=$token");
            exit();
        } else {
            $error = "Ky email nuk ekziston ne sistem.";
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Harrove Fjalekalimin | EkoKosova</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
/* Stili per container dhe form */
* { 
    box-sizing: border-box; 
    margin:0; padding:0; 
}
body{ 
    font-family:'Inter', Arial; 
    background:#f0f4f8; 
    display:flex; 
    justify-content:center; 
    align-items:center; 
    min-height:100vh; 
}
.container{ 
    background:#fff; 
    max-width:420px; 
    width:90%; 
    padding:50px 30px; 
    border-radius:16px; 
    box-shadow:0 12px 25px rgba(0,0,0,0.12); 
    text-align:center; 
}
.header-logo{ 
    font-size:32px; 
    color:#2E7D32; 
    font-weight:700; 
    margin-bottom:25px; 
}
.container h2{ 
    color:#2E7D32; 
    font-weight:600; 
    margin-bottom:15px; 
    font-size:24px; 
}
.container p{ 
    color:#555; 
    margin-bottom:30px; 
    font-size:15px; 
}
input[type=email], input[type=text]{ 
    width:100%; 
    padding:14px 16px; 
    margin-bottom:20px; 
    border-radius:8px; 
    border:1px solid #ccc; 
    font-size:15px; 
    transition:0.3s; 
}
input[type=email]:focus, input[type=text]:focus{ 
    border-color:#2E7D32; 
    outline:none; 
    box-shadow:0 0 6px rgba(46,125,50,0.3); 
}
button{ 
    width:100%; 
    padding:14px; 
    background:#2E7D32; 
    color:#fff; 
    border:none; 
    border-radius:8px; 
    font-size:16px; 
    font-weight:600; 
    cursor:pointer; 
    transition:0.3s; 
}
button:hover{ 
    background:#1b5e20; 
}
.back-login{ 
    display:inline-block; 
    margin-top:25px; 
    color:#2E7D32; 
    font-weight:600; 
    text-decoration:none; 
    transition:0.3s; 
}
.back-login:hover{ 
    text-decoration:none; 
    color:#1b5e20; 
}

/* Popup */
.popup-overlay{ 
    position: fixed; 
    top:0; 
    left:0; 
    width:100%; 
    height:100%; 
    background: rgba(0,0,0,0.5); 
    display:flex; 
    align-items:center; 
    justify-content:center; 
    z-index:9999; 
}
.popup-box{ 
    background:#f44336; 
    color:white; 
    padding:25px 40px; 
    border-radius:12px; 
    font-size:16px; 
    text-align:center; 
    position:relative;
    min-width:300px; 
}
.popup-box .close{ 
    position:absolute; 
    top:10px; 
    right:15px; 
    cursor:pointer; 
    font-weight:bold; 
}
</style>
</head>
<body>
<div class="container">
    <div class="header-logo">🌿 EkoKosova</div>
    <h2>Harrove fjalekalimin?</h2>
    <p>Shkruani email-in tuaj per te resetuar fjalekalimin.</p>

    <form method="POST">
        <input type="text" name="email" placeholder="Shkruani email-in tuaj" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        <button type="submit" name="submit">Vazhdo</button>
    </form>

    <a href="Login.php" class="back-login">⬅ Kthehu te Kyçu</a>
</div>

<?php if($error): ?>
<div class="popup-overlay">
    <div class="popup-box">
        <?= htmlspecialchars($error) ?>
        <span class="close">✖</span>
    </div>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.popup-box .close').forEach(btn=>{
    btn.addEventListener('click',()=>btn.parentElement.parentElement.style.display='none');
});
setTimeout(()=>{
    document.querySelectorAll('.popup-overlay').forEach(p=>p.style.display='none');
},6000);
</script>

</body>
</html>
