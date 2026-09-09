<?php
include 'config.php';
require_once 'blob_storage.php';
require_once 'cities.php';

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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Reports.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id, name, city, type, description, photo, created_at FROM reports WHERE id = ?");
$stmt->execute([$id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header("Location: Reports.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Detajet e Raportimit</title>
    <style>
        .report-details {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .report-details img {
            width: 100%;
            max-height: 420px;
            object-fit: cover;
        }

        .report-details-body {
            padding: 25px 30px;
        }

        .report-details-body h2 {
            margin: 0 0 10px 0;
            color: #2e7d32;
        }

        .report-details-meta {
            color: #555;
            margin-bottom: 20px;
        }

        .report-details-body p {
            line-height: 1.6;
        }

        .back-link {
            display: inline-block;
            margin: 20px auto 0 auto;
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
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
            <li><a href="Reports.php" class="active">Raportimet</a></li>
            <li><a href="contact.php">Kontakti</a></li>
            <li><a href="quotes.php">Thenie</a></li>
        </ul>

        <div class="nav-buttons">
        <?php if(isset($_SESSION['user_id'])): ?>
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

<div class="report-details">
    <?php if(!empty($report['photo'])): ?>
        <img src="<?= htmlspecialchars(resolve_upload_url($report['photo'])) ?>" alt="Foto e raportit">
    <?php else: ?>
        <img src="img/no-image.png" alt="Nuk ka foto">
    <?php endif; ?>

    <div class="report-details-body">
        <h2><?= ucfirst(htmlspecialchars($report['type'])) ?> – <?= htmlspecialchars(city_label($report['city'])) ?></h2>
        <div class="report-details-meta">
            Raportuar nga <?= htmlspecialchars($report['name']) ?> •
            <?= date('d.m.Y H:i', strtotime($report['created_at'])) ?>
        </div>
        <p><?= nl2br(htmlspecialchars($report['description'])) ?></p>

        <a href="Reports.php#shikoraporte" class="back-link">⬅ Kthehu te Raportimet</a>
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

</body>
</html>
