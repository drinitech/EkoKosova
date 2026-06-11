<?php
session_start();
include 'config.php';

// Vetëm admin mund të hyjë
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: Login.php");
    exit;
}

// Fshirja e raportit
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    $reportQuery = $conn->prepare("SELECT photo FROM reports WHERE id=?");
    $reportQuery->execute([$id]);
    $report = $reportQuery->fetch(PDO::FETCH_ASSOC);

    if($report && $report['photo'] && file_exists("uploads/".$report['photo'])){
        unlink("uploads/".$report['photo']);
    }

    $deleteQuery = $conn->prepare("DELETE FROM reports WHERE id=?");
    $deleteQuery->execute([$id]);

    header("Location: manage_reports.php");
    exit;
}

// Merr të gjitha raportimet nga databaza (të gjitha fushat)
$reportsQuery = $conn->query("SELECT id, user_id, name, email, city, type, description, photo, created_at FROM reports ORDER BY created_at ASC");
$reports = $reportsQuery->fetchAll(PDO::FETCH_ASSOC);

// Merr foto e profilit të admin
$profile_pic = 'uploads/member.png';
$user_id = $_SESSION['user_id'];
$queryPic = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
$queryPic->execute([':id' => $user_id]);
$user_pic = $queryPic->fetch(PDO::FETCH_ASSOC);
if($user_pic && $user_pic['profile_pic']){
    $profile_pic = htmlspecialchars($user_pic['profile_pic']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menaxho Raportimet | Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
    font-family: Arial, sans-serif;
}
main { 
    flex: 1; 
}
.responsive-table {
    width: 100%;
    min-width: 900px; 
    border-collapse: collapse;
}
.table-container {
    width: 95%;
    margin: 20px auto;
    overflow-x: auto;
    justify-content: center;
    display: flex;
}
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
th, td {
    padding: 12px;
    text-align: center;
    border-bottom:1px solid #ddd;
}
th {
    background:#2E7D32;
    color:#fff;
    text-transform: uppercase;
}
td img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}
.btn, .btn_edit {
    padding: 6px 12px;
    border-radius: 5px;
    color:#fff;
    text-decoration: none;
    margin: 0 2px;
    display: inline-block;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.btn.delete{ 
    background: #f44336; 
}
.btn.delete:hover{ 
    background: #da190b; 
}
.btn_edit{ 
    background: #2196F3; 
}
.btn_edit:hover{
    background: #0b7dda; 
}
.modal{
    display:none; 
    position:fixed; 
    z-index:1000; 
    left:0; top:0;
    width:100%; height:100%; 
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
    position:absolute; top:10px; right:15px;
    font-size:24px; cursor:pointer; color:#999; transition:0.3s;
}
.modal .close:hover{ 
    color:#333; 
}
.modal h3{ 
    color:#c0392b; 
    margin-bottom:10px; 
}
.modal p{ 
    margin-bottom:20px; 
    color:#333; 
}
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
.modal .btn.delete:hover{ 
    background:#da190b; 
}
.modal .btn.cancel{ 
    background:green; 
    color:white; 
    border:none; 
}
.modal .btn.cancel:hover{ 
    background:#95a5a6; 
}
footer.footer {
    background-color: rgb(27, 79, 47);
    color: #fff;
    padding: 40px 20px 20px 20px;
    margin-top: auto;
}
.footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 20px;
}
.footer-about, .footer-links, .footer-contact{ 
    flex:1; min-width: 150px; 
}
.footer h3.logo{ 
    margin:0; 
}
.footer h4{ 
    margin-bottom: 8px; 
}
.footer p, .footer li, .footer a{ 
    font-size: 14px; color:#fff; 
}
.footer a:hover{ 
    text-decoration: underline; 
}
.footer-bottom{ 
    text-align:center; 
    margin-top:20px; 
    font-size: 13px; 
}

/* ------------- RESPONSIVE -------------*/

@media screen and (max-width: 460px) {

    .table-container {
        width: 100%;
        overflow-x: hidden;
    }

    table {
        width: 100%;
        table-layout: fixed;
        font-size: 12px;
        border-collapse: collapse;
    }

    th, td {
        padding: 6px;
        text-align: center;
        word-break: break-word; 
        white-space: normal;      
    }

    td img {
        width: 30px;
        height: 30px;
        object-fit: cover;
    }

    .btn, .btn_edit {
        padding: 3px 6px;
        font-size: 10px;
        white-space: nowrap;
    }

    footer.footer{ 
        padding: 30px 15px; 
    }
    .footer-container{ 
        flex-direction: column; 
        align-items: center; 
        gap: 15px; 
    }
    .footer h3.logo{ 
        font-size: 18px; 
    }
    .footer h4{ 
        font-size: 13px; 
    }
    .footer p, .footer li, .footer a{ 
        font-size: 12px; 
    }
    .footer-bottom{ 
        font-size: 11px; 
    }
}

@media screen and (max-width: 360px) {

    .table-container {
        width: 100%;
        padding: 0 5px;
        overflow-x: hidden;
    }

    table {
        width: 100%;
        table-layout: fixed;
        font-size: 9px;
    }

    th, td {
        padding: 3px;
        text-align: center;
        word-break: break-word;
        white-space: normal;
    }

    td img {
        width: 20px;
        height: 20px;
        border-radius: 5px;
    }

    .btn, .btn_edit {
        padding: 1px 3px;
        font-size: 8px;
        white-space: nowrap;
    }

    footer.footer {
        padding: 20px 5px;
        text-align: center;
    }

    .footer-container {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .footer h3.logo{ 
        font-size: 14px; 
    }
    .footer h4{ 
        font-size: 11px; 
    }
    .footer p, .footer li, .footer a{ 
        font-size: 10px; 
    }
    .footer-bottom{ font-size: 9px; 
}
}

@media screen and (max-width: 850px) {

    .responsive-table {
        min-width: 700px;
        font-size: 13px;
    }

    th, td {
        padding: 8px;
        white-space: normal;
        word-break: break-word;
    }

    td.message {
        min-width: 200px;
    }

    .btn, .btn_edit {
        padding: 4px 6px;
        font-size: 11px;
    }
}
/* 16" MacBook Pro dhe ekrane shumë të mëdha */
@media screen and (min-width: 1601px){
    table{ 
        width: 90%; 
        font-size: 16px; 
        min-width: 1200px; 
    }
    th, td{ 
        padding: 14px; 
    }
    table img{ 
        width: 60px; 
        height: 60px; 
    }
    .btn, .btn_edit{ 
        padding: 8px 16px; 
        font-size: 14px; 
    }
    footer.footer{ 
        padding: 50px 40px; 
    }
    .footer-container{ 
        gap: 30px; 
    }
    .footer h3.logo{ 
        font-size: 24px; 
    }
    .footer h4{ 
        font-size: 18px; 
    }
    .footer p, .footer li, .footer a{ 
        font-size: 16px; 
    }
    .footer-bottom{ 
        font-size: 14px; 
    }
}

/* 14" MacBook Pro */
@media screen and (min-width: 1440px) and (max-width: 1600px) {
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
        padding: 6px 12px; 
        font-size: 13px; 
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

/* 13" MacBook Pro */
@media screen and (min-width: 1280px) and (max-width: 1439px) {
    table{ 
        width: 95%; 
        font-size: 14px; 
        min-width: 1000px; 
    }
    th, td{ 
        padding: 10px; 
    }
    table img{ 
        width: 50px; 
        height: 50px; 
    }
    .btn, .btn_edit{ 
        padding: 6px 12px; 
        font-size: 12px; 
    }
    footer.footer{ 
        padding: 40px 25px; 
    }
    .footer-container{ 
        gap: 20px; 
    }
    .footer h3.logo{ 
        font-size: 20px; 
    }
    .footer h4{ 
        font-size: 15px; 
    }
    .footer p, .footer li, .footer a{ 
        font-size: 14px; 
    }
    .footer-bottom{ 
        font-size: 12px; 
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
    </div>
</nav>
</header>
<main>
<h1 style="text-align:center">👥 Menaxhimi i Raportimeve</h1>
<div class="table-container">
<table>
<tr>
    <th>ID</th>
    <th>User ID</th>
    <th>Emri</th>
    <th>Email</th>
    <th>Qyteti</th>
    <th>Lloji</th>
    <th>Përshkrimi</th>
    <th>Foto</th>
    <th>Krijuar</th>
    <th>Veprime</th>
</tr>
<?php foreach($reports as $r): ?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= $r['user_id'] ?></td>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= htmlspecialchars($r['email']) ?></td>
    <td><?= htmlspecialchars($r['city']) ?></td>
    <td><?= htmlspecialchars($r['type']) ?></td>
    <td class="description" data-full="<?= htmlspecialchars($r['description']) ?>">
        <?= htmlspecialchars(substr($r['description'],0,50)) ?>...
    </td>
    <td>
        <?php if($r['photo']): ?>
            <img src="uploads/<?= htmlspecialchars($r['photo']) ?>" alt="foto raportit">
        <?php endif; ?>
    </td>
    <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
    <td>
        <a href="edit_reports.php?id=<?= $r['id'] ?>" class="btn_edit">✏️</a>
        <a href="#" class="btn delete" data-id="<?= $r['id'] ?>">🗑️</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
</div>
</main>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Jeni të sigurt?</h3>
        <p>Ky raport do të fshihet përgjithmonë.</p>
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
