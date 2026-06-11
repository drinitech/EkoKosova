<?php
session_start();
include 'config.php';


if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: Login.php");
    exit;
}

$result = $conn->query("SELECT id, name, username, email, is_admin FROM users");

$profile_pic = 'uploads/member.png'; 

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    $queryPic = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
    $queryPic->bindParam(':id', $user_id, PDO::PARAM_INT); // e lidh vleren reale te $user_id me :id PDO::PARAM_INT tregon qe eshte integer
    $queryPic->execute(); // ekzekutohet
    $user_pic = $queryPic->fetch(PDO::FETCH_ASSOC); // kur ekzekutohet merr nje array asociative ku qdo kolon nga databaza ka me jep nje key ne kete array me emer tvet

    if($user_pic && $user_pic['profile_pic']){ // e kontrollon nese useri ekziston edhe kontrollon nese fusha e fotos ka vlere pra nese eshte ngarkuar foto perndryshe nuk vazhdon
        $profile_pic = htmlspecialchars($user_pic['profile_pic']);
    }                 // e mbron nga XSS
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | EkoKosova</title>
<link rel="stylesheet" href="style.css"> 
<style>

/* Reset dhe setup bazik */
html, body {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* footer gjithmonë në fund */
}

/* Main content zgjerohet për të shtyrë footer-in poshtë */
.main-content {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px;
    box-sizing: border-box;
}

/* Dashboard me lartësi fikse dhe responsive */
.dashboard-container {
    border: 3px solid #4CAF50; 
    padding: 30px 20px;
    border-radius: 10px;
    width: 100%;
    max-width: 1200px;
    background-color: #ffffff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    box-sizing: border-box;
    margin-top: 120px;
}

/* Dashboard label */
.dashboard-label {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    color: #4CAF50;
    margin-bottom: 20px; 
}

/* Buttons flex dhe responsive */
.dashboard-buttons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
}

.dashboard-buttons .btn {
    padding: 15px 30px;
    background: linear-gradient(90deg, #4CAF50, #2E7D32);
    color: white;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
    text-align: center;
}

.dashboard-buttons .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 10px rgba(0,0,0,0.3);
}

/* Footer */
.footer {
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

@media screen and (max-width: 767px) {
    .dashboard-container {
        margin-top: 50px;
        padding: 20px 15px;
    }

    .dashboard-label {
        font-size: 18px;
    }

    .dashboard-buttons {
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .dashboard-buttons .btn {
        width: 300px;   
        height: 60px;
        margin-bottom: 15px;
        
        display: flex;       
        justify-content: center;
        align-items: center; 
        text-align: center; 
    }

    .footer-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-about, .footer-links, .footer-contact {
        flex: 1 1 100%;
        text-align: center;
        margin-bottom: 15px;
    }

    .footer h3.logo { font-size: 18px; }
    .footer h4 { font-size: 14px; }
    .footer p, .footer li, .footer a { font-size: 12px; }
    .footer-bottom { font-size: 11px; }
}

/* Responsive: Tablet (768px - 1024px) */
@media screen and (min-width: 768px) and (max-width: 1024px) {
    .dashboard-buttons {
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .dashboard-buttons .btn {
        width: 300px;  
        height: 60px;
        margin-bottom: 15px;
        
        display: flex;       
        justify-content: center;
        align-items: center;
        text-align: center;    
    }
    .footer {
        padding: 15px 20px;
        text-align: center;
    }

    .footer-container {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .footer-about, .footer-links, .footer-contact {
        flex: 1 1 100%; 
        text-align: center; 
        margin-bottom: 10px;
    }

    .footer h3.logo {
        font-size: 18px;
    }

    .footer h4 {
        font-size: 14px;
    }

    .footer p, .footer li, .footer a {
        font-size: 13px;
    }

    .footer-bottom {
        font-size: 11px;
        margin-top: 8px;
        padding-top: 6px;
        border-top: 1px solid rgba(255,255,255,0.3);
    }
}

/* Desktop (1025px - 1440px) */
@media screen and (min-width: 1025px) and (max-width: 1440px) {
    .dashboard-container { margin-top: 100px; }
    .footer-container {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .footer h3.logo { font-size: 24px; }
    .footer h4 { font-size: 17px; }
    .footer p, .footer li, .footer a { font-size: 15px; }
    .footer-bottom { font-size: 14px; }
}

/* Large screens (>1440px, MacBook 16", iPhone 16 Pro Max landscape) */
@media screen and (min-width: 1441px) {
    .dashboard-container { margin-top: 120px; }
    .footer-container {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .footer h3.logo { font-size: 26px; }
    .footer h4 { font-size: 18px; }
    .footer p, .footer li, .footer a { font-size: 16px; }
    .footer-bottom { font-size: 15px; }
}

/* MacBook 16" 1600px+ */
@media (min-width: 1601px) {
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
<main class="main-content">
    <div class="dashboard-container">
        <div class="dashboard-label">Quick Actions</div>
        <div class="dashboard-buttons">
            <a href="manage_users.php" class="btn">👥 Menaxho Userat</a>
            <a href="manage_reports.php" class="btn">📄 Menaxho Raportimet</a>
            <a href="manage_contacts.php" class="btn">📞 Menaxho Kontaktimet</a>
            <a href="konfigurimet.php" class="btn">⚙️ Konfigurimet</a>
        </div>
    </div>
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
