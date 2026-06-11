<?php
session_start();
include 'config.php';

$error = "";
$showModal = false; // e fsheh popup

if (!isset($_GET['token'])) {
    die("Token nuk ekziston.");
}

$token = $_GET['token']; // e gjen nje random token qe sherben per me ndrru passwordin

$userQuery = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
$userQuery->execute([$token]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Token i pavlefshem.");
}

if (strtotime($user['reset_expires']) < time()) {
    die("Ky link ka skaduar.");
}

if (isset($_POST['submit'])) {
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    if (empty($password) || empty($confirm)) {
        $error = "Ju lutem plotesoni te gjitha fushat!";
    }
    elseif ($password !== $confirm) {
        $error = "Fjalekalimet nuk perputhen!";
    }
    elseif (
        strlen($password) < 6 ||
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W_]/', $password)
    ) {
        $error = "⚠️ Fjalekalimi duhet te kete minimum 6 karaktere, nje shkronje te madhe, nje numer dhe nje simbol.";
    }
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT); // kjo pjes perdoret per me mshef passwordin 
        $now = (new DateTime('now', new DateTimeZone('Europe/Belgrade')))->format('Y-m-d H:i:s'); // e qet kohen reale 
    
        $update = $conn->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_expires = NULL, password_changed_at = ?
            WHERE id = ? AND reset_token = ?
        "); // kur te perfundohet ndrrimi i passwordit vlera e tokenit kthehet null pra smund te hapet prap
        $update->execute([$hash, $now, $user['id'], $token]); // behen update passwordi ora id e userit edhe tokeni
    
        $showModal = true;
    }    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ndrysho fjalekalimin | EkoKosova</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Inter', Arial, sans-serif;
    background: #f0f4f8;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

.container {
    background: #fff;
    max-width: 400px;
    width: 90%;
    padding: 40px 30px;
    border-radius: 16px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.12);
    text-align: center;
}

.container h2 {
    color: #2E7D32;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 24px;
}

.container p {
    color: #555;
    margin-bottom: 25px;
    font-size: 15px;
}

input {
    width: 90%;
    padding: 14px 16px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
    transition: 0.3s;
}

input {
    border-color: #2E7D32;
    outline: none;
    box-shadow: 0 0 6px rgba(46,125,50,0.3);
}

button {
    width: 70%;
    padding: 14px;
    background: #2E7D32;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #1b5e20;
}

.error {
    background: #ffebeb;
    color: #d8000c;
    border-left: 5px solid #d8000c;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
    text-align: left;
}

.modal {
    display: none;
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.6);
}

.modal-content {
    background:#fff;
    max-width:350px;
    background-color: #d1e7dd;
    margin: 15% auto;
    padding: 30px 25px;
    border-radius:12px;
    text-align:center;
}

.modal-content h2 {
    margin-bottom: 10px;
}

.modal-content button {
    margin-top:20px;
    width:100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    background: #2E7D32;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

.modal-content button:hover {
    background: #1b5e20;
}

</style>
</head>
<body>

<?php if(!$showModal): ?>
<div class="container">
    <h2>Ndrysho fjalekalimin</h2>
    <p>Shkruani fjalekalimin e ri per llogarine tuaj.</p>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Fjalekalimi i ri" >
        <input type="password" name="confirm" placeholder="Konfirmo fjalekalimin" >
        <button type="submit" name="submit">Ndrysho</button>
    </form>
</div>
<?php endif; ?>

<?php if($showModal): ?>
<div id="successModal" class="modal">
    <div class="modal-content">
        <h2>✅ Sukses</h2>
        <p>Fjalekalimi u ndryshua me sukses!</p>
        <button id="okBtn">OK</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const modal = document.getElementById("successModal");
    modal.style.display = "block";

    document.getElementById("okBtn").onclick = function(){
        window.location.href = "Login.php";
    };
});
</script>
<?php endif; ?>

</body>
</html>
