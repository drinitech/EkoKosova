<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: Login.php");
    exit;
}

if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    $qutoesQuery = $conn->prepare("SELECT autor_img FROM quotes WHERE id=?");
    $qutoesQuery->execute([$id]);
    $quote = $qutoesQuery->fetch(PDO::FETCH_ASSOC);

    if($quote && $quote['autor_img'] && file_exists($quote['autor_img'])){
        unlink($quote['autor_img']);
    }

    $qutoesQuery = $conn->prepare("DELETE FROM quotes WHERE id=?");
    $qutoesQuery->execute([$id]);

    header("Location: konfigurimet.php");
    exit;
}

$qutoesQuery = $conn->query("SELECT * FROM quotes ORDER BY id ASC");
$quotes = $qutoesQuery->fetchAll(PDO::FETCH_ASSOC);


$profile_pic = 'uploads/member.png'; 

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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfigurimet | EkoKosova</title>
<link rel="stylesheet" href="style.css">
<style>
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

/* Contenti kryesor */
main {
    flex: 1; /* Merr pjesën tjetër të faqes */
}
table {
    width: 80%;
    border-collapse: collapse;
    margin: 30px auto;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    table-layout: fixed;
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    word-wrap: break-word;
}

th {
    background: #2E7D32;
    color: #fff;
    text-transform: uppercase;
}

td img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}
a{ 
    color:#2196F3; 
    text-decoration:none; 
}
a:hover{ 
    text-decoration:none; 
}

.close {
    float: right;
    font-size: 22px;
    cursor: pointer;
}

.btn {
    padding: 6px 12px;
    border-radius: 5px;
    color: #fff;
    text-decoration: none;
    margin: 0 2px;
    display: inline-block;
    transition: 0.3s;
    font-weight: bold;
    cursor: pointer;
}

.btn.edit {
    background: #2196F3;
}

.btn.edit:hover {
    background: #0b7dda;
}

.btn.delete {
    background: #f44336;
}

.btn.delete:hover {
    background: #da190b;
}

.modal{
    display:none;
    position:fixed;
    z-index:1000;
    left:0;
    top:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.6);
}
.modal-content{
    background:#fff;
    margin:15% auto;
    padding:25px 30px;
    border-radius:12px;
    max-width:400px;
    text-align:center;
    position:relative;
}
.modal .close{
    position:absolute;
    top:10px;
    right:15px;
    font-size:24px;
    cursor:pointer;
    color:#999;
    transition:0.3s;
}
.modal .close:hover{ color:#333; }
.modal h3{ color:#c0392b; margin-bottom:10px; }
.modal p{ margin-bottom:20px; color:#333; }
.modal .btn{
    padding:10px 20px;
    border-radius:8px;
    font-weight:bold;
    text-decoration:none;
    transition:0.3s;
    cursor:pointer;
}
.modal .btn.delete{
    background:#f44336;
    color:white;
}
.modal .btn.delete:hover{ background:#da190b; }
.modal .btn.cancel{
    background:green;
    color:white;
    border:none;
}
.modal .btn.cancel:hover{ background:#95a5a6; }


/*Footer*/
footer {
    background-color: rgb(27, 79, 47);
    color: #ffffff;
    padding: 40px 20px 20px 20px;
}

.footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
}

.footer-about,.footer-links,.footer-contact {
    flex: 1 1 250px;
    margin-bottom: 20px;
}

.footer h3, .footer h4 {
    margin-bottom: 15px;
}

.footer p, .footer ul, .footer li, .footer a {
    font-size: 14px;
    color: #ffffff;
    text-decoration: none;
}

.footer-links ul {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a:hover {
    text-decoration: underline;
}

.footer-bottom {
    text-align: center;
    border-top: 1px solid #ffffff50;
    padding-top: 15px;
    margin-top: 20px;
    font-size: 13px;
    color: #ffffffaa;
}

@media (max-width: 480px) {
    table { font-size: 10px; width: 100%; }
    th, td { padding: 4px 5px; }
    td img { width: 20px; height: 20px; }
    table a.btn { padding: 2px 4px; font-size: 8px; }

    .footer-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 10px;
    }

    .footer-about h3.logo { font-size: 16px; }
    .footer-about p, .footer-links li, .footer-contact p { font-size: 12px; }
    .footer-bottom { font-size: 11px; }
}

/* Mobile medium / tablets (768px - 1024px) */
@media (min-width: 768px) and (max-width: 1024px){
    table { font-size: 12px; width: 100%; }
    th, td { padding: 5px 4px; }
    td img { width: 25px; height: 25px; }
    table a.btn { padding: 3px 6px; font-size: 10px; }

 .footer-container {
        flex-direction: column; /* kolonat mbi njëra-tjetrën */
        align-items: center;    /* qendër horizontale */
        text-align: center;     /* qendër teksti */
        gap: 15px;              /* hapësirë midis kolonave */
    }

    .footer-about, .footer-links, .footer-contact {
        flex: 1 1 100%;
        margin-bottom: 15px;
    }

    .footer h3.logo {
        font-size: 20px;
        margin-bottom: 10px;
    }

    .footer h4 {
        font-size: 16px;
        margin-bottom: 8px;
    }

    .footer p, .footer li, .footer a {
        font-size: 14px;
        line-height: 1.5;
    }

    .footer-bottom {
        font-size: 13px;
        margin-top: 10px;
        text-align: center;
    }
}

@media (min-width: 1280px) and (max-width: 1366px) {

    /* HEADER */
    .navbar .logo { 
        font-size: 28px; 
    }
    .navbar ul.nav-links li a { 
        font-size: 18px; 
        padding: 10px 15px; 
    }
    .navbar .nav-buttons a, 
    .navbar .nav-buttons button { 
        font-size: 18px; 
        padding: 12px 20px; 
    }

    /* FOOTER */
    .footer-container {
        flex-direction: row;
        justify-content: space-around; 
        align-items: flex-start;
        gap: 40px;
    }

    .footer-about, .footer-links, .footer-contact {
        flex: 1 1 33%;
        text-align: center; 
    }

    .footer h3.logo {
        font-size: 32px; 
    }

    .footer h4 {
        font-size: 22px; 
    }

    .footer p, .footer li, .footer a {
        font-size: 18px;
        line-height: 1.8;
    }

    .footer-bottom {
        font-size: 16px;
        padding-top: 20px;
        margin-top: 20px;
        text-align: center;
    }
}

@media (min-width: 540px) and (max-width: 1024px){
    table{ 
        font-size: 12px; 
        width: 95%; 
    }
    th, td{ 
        padding: 6px 5px; 
    }
    td img{ 
        width: 30px; 
        height: 30px; 
    }
    table a.btn{ 
        padding: 4px 8px; 
        font-size: 10px; 
    }

    .footer-container{
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 15px;
    }

    .footer h3.logo{ 
        font-size: 20px; 
    }
    .footer h4{ 
        font-size: 16px; 
    }
    .footer p, .footer li, .footer a{ 
        font-size: 14px; 
    }
    .footer-bottom{ 
        font-size: 13px; 
    }
}

@media screen and (min-width: 768px) and (max-width: 1024px) {
    .footer {
        padding: 15px 20px; 
    }

    .footer-container {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-around; 
        align-items: flex-start;
        gap: 10px; 
    }

    .footer-about, .footer-links, .footer-contact {
        flex: 1 1 30%; 
        min-width: 200px; 
        margin-bottom: 10px;
        text-align: left; 
    }

    .footer h3.logo {
        font-size: 20px;
    }

    .footer h4 {
        font-size: 16px;
    }

    .footer p, .footer li, .footer a {
        font-size: 14px;
    }

    .footer-bottom {
        margin-top: 10px;
        padding-top: 6px;
        width: 100%;
        text-align: center;
        border-top: 1px solid rgba(255,255,255,0.3);
        font-size: 12px;
    }
}



@media (min-width: 1440px) and (max-width: 1600px) {
    table{ 
        font-size: 15px; 
        width: 80%; 
    }
    th, td{ 
        padding: 10px 8px; 
    }
    td img{ 
        width: 40px; 
        height: 40px; 
    }
    table a.btn{ 
        padding: 6px 12px; font-size: 13px; 
    }
    .footer-container{
        flex-direction: row;
        justify-content: center;
        align-items: center;
    }

    .footer-about, .footer-links, .footer-contact {
        margin-bottom: 0;
    }

    .footer h3.logo {
        font-size: 24px;
    }

    .footer h4 {
        font-size: 17px;
    }

    .footer p, .footer li, .footer a {
        font-size: 15px;
    }

    .footer-bottom {
        font-size: 14px;
    }
}

/* MacBook 16" */
@media (min-width: 1601px) {
    table{ 
        font-size: 16px; 
        width: 80%; 
    }
    th, td{ 
        padding: 12px; 
    }
    td img{ 
        width: 50px; 
        height: 50px; 
    }
    table a.btn{ 
        padding: 6px 12px; 
        font-size: 14px; 
    }
    .footer-container {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    .footer h3.logo {
        font-size: 26px;
    }

    .footer h4 {
        font-size: 18px;
    }

    .footer p, .footer li, .footer a {
        font-size: 16px;
    }

    .footer-bottom {
        font-size: 15px;
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



<main>



<h2 style="text-align:center;">Lista e Thenieve</h2>
<table>
<tr>
    <th>ID</th>
    <th>Thenie</th>
    <th>Autor</th>
    <th>Foto</th>
    <th>Veprime</th>
</tr>
<?php foreach($quotes as $q): ?>
<tr>
    <td><?= $q['id'] ?></td>
    <td><?= htmlspecialchars($q['thenje_text']) ?></td>
    <td><?= htmlspecialchars($q['autori']) ?></td>
    <td>
        <?php if($q['autor_img']): ?>
            <img src="<?= $q['autor_img'] ?>" alt="foto autor">
        <?php endif; ?>
    </td>
    <td>
        <a class="btn edit" href="edit_quotes.php?id=<?= $q['id'] ?>">✏️</a>
        <a class="btn delete" href="#" data-id="<?= $q['id'] ?>">🗑️</a>
    </td>

</tr>
<?php endforeach; ?>
</table>
</main>
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-icon">🗑️</div>
        <h3>Jeni te sigurt?</h3>
        <p>Po perpiqeni te fshini kete thenie. Ky veprim nuk mund te kthehet.</p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <a href="#" id="confirmDelete" class="btn delete">Fshij</a>
            <button class="btn cancel">Anulo</button>
        </div>
    </div>
</div>

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
document.addEventListener('DOMContentLoaded', function(){
    const deleteModal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = deleteModal.querySelector('.btn.cancel');
    const closeBtn = deleteModal.querySelector('.close');

    const deleteLinks = document.querySelectorAll('.btn.delete[data-id]');

    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e){
            e.preventDefault();
            const id = this.dataset.id;
            confirmBtn.href = "?delete=" + id;
            deleteModal.style.display = 'block';
        });
    });

    cancelBtn.addEventListener('click', () => deleteModal.style.display = 'none');
    closeBtn.addEventListener('click', () => deleteModal.style.display = 'none');
    window.addEventListener('click', e => {
        if(e.target === deleteModal) deleteModal.style.display = 'none';
    });
});
</script>

</body>
</html>
