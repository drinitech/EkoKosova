<?php
session_start();
include 'config.php';

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

$latestReports = $conn->prepare("
    SELECT name, city, type, description, photo, created_at
    FROM reports
    ORDER BY created_at DESC
    LIMIT 3
");
$latestReports->execute();
$reports = $latestReports->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">🌿 EkoKosova</div>

        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon">&#9776;</label>

        <ul class="nav-links">
            <li><a href="index.php" class="active">Ballina</a></li>
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

<!-- HERO SLIDER -->
<section class="hero-slider">

  <div class="slide fade">
    <img src="img/SliderFoto1.avif">
    <div class="caption">
      <h1>Mbro Natyrën</h1>
      <p>Raporto ndotjen dhe kontribuo për një Kosovë më të pastër.</p>
      <a href="Reports.php#shikoraporte" class="btn">Shiko Raportet</a>
    </div>
  </div>

  <div class="slide fade">
    <img src="img/SliderFoto2.avif">
    <div class="caption">
      <h1>Bëhu pjesë e ndryshimit</h1>
      <p>Çdo raport i yti ndihmon komunitetin dhe natyrën.</p>
      <a href="about.php" class="btn">Rreth Nesh</a>
    </div>
  </div>

  <div class="slide fade">
    <img src="img/SliderFoto3.avif">
    <div class="caption">
      <h1>Ruaj të ardhmen</h1>
      <p>Monitoro lokacionet e ndotura përmes hartës interaktive.</p>
      <a href="Reports.php" class="btn">Harta Raporteve</a>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <h2>Pse EkoKosova?</h2>
  <div class="feature-grid">
    <div class="f-box"><h3>📸 Raportim i Shpejtë</h3></div>
    <div class="f-box"><h3>🌍 Harta Interaktive</h3></div>
    <div class="f-box"><h3>⏳ Gjurmim i Statusit</h3></div>
  </div>
</section>

<!-- LATEST REPORTS -->
<section class="latest-reports" id="shikoraporte">
  <h2>Raportimet e Fundit</h2>

  <div class="reports-row">

    <?php if(count($reports) > 0): ?>
        <?php foreach($reports as $report): ?>
            <div class="report-card">

                <?php if(!empty($report['photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($report['photo']); ?>">
                <?php else: ?>
                    <img src="uploads/member.png">
                <?php endif; ?>

                <h4>
                    <?php echo ucfirst(htmlspecialchars($report['type'])); ?> –
                    <?php echo ucfirst(htmlspecialchars($report['city'])); ?>
                </h4>

                <p>
                    <?php echo htmlspecialchars(substr($report['description'], 0, 90)); ?>...
                </p>

                <small>
                    <?php echo htmlspecialchars($report['name']); ?> •
                    <?php echo date('d.m.Y', strtotime($report['created_at'])); ?>
                </small>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">Nuk ka ende raporte 📭</p>
    <?php endif; ?>

  </div>
</section>

<script>
let slideIndex = 0;
showSlides();
function showSlides() {
  let slides = document.getElementsByClassName("slide");
  for (let i = 0; i < slides.length; i++) slides[i].style.display = "none";
  slideIndex++;
  if (slideIndex > slides.length) slideIndex = 1;
  slides[slideIndex-1].style.display = "block";
  setTimeout(showSlides, 3800);
}
</script>

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
