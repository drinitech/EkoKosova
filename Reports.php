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


$success = '';
$error = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); 
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); 
}

// Këtu vazhdon logjika e raportit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Duhet të jeni të kyçur për të raportuar ❌";
        header("Location: Reports.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $type = trim($_POST['type']);
    $description = trim($_POST['description']);

    if ($name == "" || $email == "" || $city == "" || $type == "" || $description == "") {
        $_SESSION['error'] = "Ju lutem plotesoni te gjitha fushat ⚠️";
        header("Location: Reports.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email-i nuk eshte valid ❌";
        header("Location: Reports.php");
        exit;
    }

    // Kontrollo email-in e user-it
    $stmtUser = $conn->prepare("SELECT email FROM users WHERE id = :id LIMIT 1");
    $stmtUser->execute([':id' => $user_id]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $userEmail = $userData['email'] ?? '';

    if ($email != $userEmail) {
        $_SESSION['error'] = "Email-i nuk perputhet me email-in tuaj ne sistem ❌";
        header("Location: Reports.php");
        exit;
    }

    // Foto
    if (empty($_FILES['photo']['name'])) {
        $_SESSION['error'] = "Duhet te ngarkoni patjeter nje foto ❌";
        header("Location: Reports.php");
        exit;
    } else {
        $photoName = time() . "_" . basename($_FILES['photo']['name']);
        $storedPhoto = save_uploaded_file($_FILES['photo']['tmp_name'], $photoName);

        if (!$storedPhoto) {
            $_SESSION['error'] = "Gabim gjate ngarkimit te fotos ❌";
            header("Location: Reports.php");
            exit;
        }
    }

    $profile_pic = 'img/member.png'; // default

    if(isset($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && !empty($user['profile_pic'])){
            // Nëse ruan vetëm emrin e file-it
            $profile_pic = 'uploads/' . htmlspecialchars($user['profile_pic']);
        }
    }

    // Ruaj raportin
    $stmt = $conn->prepare("
        INSERT INTO reports (user_id, name, email, city, type, description, photo)
        VALUES (:user_id, :name, :email, :city, :type, :description, :photo)
    ");

    if ($stmt->execute([
        ':user_id' => $user_id,
        ':name' => $name,
        ':email' => $email,
        ':city' => $city,
        ':type' => $type,
        ':description' => $description,
        ':photo' => $storedPhoto
    ])) {
        $_SESSION['success'] = "Raporti u dergua me sukses ✅";
        header("Location: Reports.php");
        exit;
    } else {
        $_SESSION['error'] = "Gabim gjate ruajtjes se raportit ❌";
        header("Location: Reports.php");
        exit;
    }
}


$allReportsStmt = $conn->query("
    SELECT id, name, city, type, description, photo, created_at
    FROM reports
    ORDER BY created_at DESC
");
$reports = $allReportsStmt->fetchAll(PDO::FETCH_ASSOC);
$allReports = $reports;

$mapByCity = [];
foreach ($allReports as $r) {
    if (!isset($eko_city_coords[$r['city']])) continue; // qytet i panjohur, s'ka koordinata

    $mapByCity[$r['city']]['coords'] = $eko_city_coords[$r['city']];
    $mapByCity[$r['city']]['label'] = city_label($r['city']);
    $mapByCity[$r['city']]['reports'][] = [
        'id' => $r['id'],
        'type' => ucfirst($r['type']),
        'date' => date('d.m.Y', strtotime($r['created_at'])),
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <title>Raportimet</title>
    <style>
        #reports-map {
            height: 420px;
            border-radius: 10px;
            margin-top: 10px;
        }
        .success-message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .error-message {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-bottom: 15px;
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

<section class="hero">
    <div>
        <h2>“Mbrojme Natyren, Permiresojme Kosoven”</h2>
        <p>Raporto ndotjet dhe ndihmo komunitetin te kete nje mjedis me të pastër</p>
        <a href="Reports.php#reportform" class="btn">Raporto Tani</a>
    </div>
</section>

<section class="report-section">
    <h2 id="reportform">Raporto Ndotjen</h2>

    <!-- Mesazhet e suksesit ose gabimit -->
    <?php if(!empty($success)): ?>
    <div class="alert alert-success">
        <!-- Ikonë ✅ -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if(!empty($error)): ?>
    <div class="alert alert-error">
        <!-- Ikonë ❌ -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <?php echo $error; ?>
    </div>
<?php endif; ?>


    <form class="report-form" method="POST" enctype="multipart/form-data">
        <label for="name">Emri Juaj</label>
        <input type="text" id="name" name="name" placeholder="Shkruaj emrin tuaj">

        <label for="email">Email</label>
        <input type="text" id="email" name="email" placeholder="Shkruaj emailin tuaj">

        <label for="city">Qyteti</label>
        <select id="city" name="city">
            <option value="">Zgjedh qytetin</option>
            <?php foreach ($eko_cities as $slug => $label): ?>
                <option value="<?= $slug ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="type">Lloji i Ndotjes</label>
        <select id="type" name="type">
            <option value="">Zgjedh llojin</option>
            <option value="ajri">Ajri</option>
            <option value="ujit">Uji</option>
            <option value="tokes">Toka</option>
        </select>

        <label for="description">Përshkrimi i Ndotjes</label>
        <textarea id="description" name="description" placeholder="Përshkruaj ndotjen..."></textarea>

        <label for="photo">Ngarko Foto</label>
        <input type="file" id="photo" name="photo" accept="image/*">

        <button type="submit" name="submit_report" id="submit">Dërgo Raportin</button>
    </form>


</section>

<section class="map-section" id="harta">
    <h2 style="text-align:center;">Harta e Raportimeve</h2>
    <div id="reports-map"></div>
</section>

<section class="latest-reports" id="shikoraporte">
  <h2>Të Gjitha Raportimet</h2>

  <div class="reports-row">

    <?php if(count($reports) > 0): ?>
        <?php foreach($reports as $report): ?>
            <a href="report_details.php?id=<?= $report['id'] ?>" class="report-card">

                <?php if(!empty($report['photo'])): ?>
                    <img src="<?php echo htmlspecialchars(resolve_upload_url($report['photo'])); ?>">
                <?php else: ?>
                    <img src="img/no-image.png">
                <?php endif; ?>

                <h4>
                    <?php echo ucfirst(htmlspecialchars($report['type'])); ?> –
                    <?php echo htmlspecialchars(city_label($report['city'])); ?>
                </h4>

                <p>
                    <?php echo htmlspecialchars(substr($report['description'], 0, 90)); ?>...
                </p>

                <small>
                    <?php echo htmlspecialchars($report['name']); ?> •
                    <?php echo date('d.m.Y', strtotime($report['created_at'])); ?>
                </small>

            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">Nuk ka ende raporte 📭</p>
    <?php endif; ?>

  </div>
</section>
<section class="tips-section">
    <h2 id="keshilla">Këshilla Mjedisore për Shëndetin</h2>
    <div class="tips-container">
        <div class="tip-card">
            <img src="img/riciklimi.jpg" alt="Riciklimi" class="tip-img">
            <h3>Riciklo Mbeturinat</h3>
            <p>Ndihmo të mbash ambientin të pastër duke ndarë mbeturinat dhe ricikluar sa më shumë materiale të mundshme.</p>
        </div>
        <div class="tip-card">
            <img src="img/ajripastert.jpg" alt="Ajri i pastër" class="tip-img">
            <h3>Mbro Ajrin</h3>
            <p>Shfrytëzo transportin publik, biçikletën ose ecjen për të reduktuar emetimet e automjeteve.</p>
        </div>
        <div class="tip-card">
            <img src="img/energjia.png" alt="Energjia" class="tip-img">
            <h3>Kurse Energji</h3>
            <p>Përdor energji të ripërtëritshme dhe fik pajisjet kur nuk i përdor për të reduktuar ndotjen dhe shpenzimet.</p>
        </div>
        <div class="tip-card">
            <img src="img/pemet.jpg" alt="Pemë" class="tip-img">
            <h3>Shto Hije dhe Pemë</h3>
            <p>Mbjellja e pemëve ndihmon në pastrimin e ajrit dhe ul stresin, duke kontribuar në një mjedis më të shëndetshëm.</p>
        </div>
    </div>
</section>

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
  // zgjedh te gjitha alert-at
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';

        // pas 0.5s (koha e transition), hiq alert nga DOM
        setTimeout(() => {
            alert.remove();
        }, 500);
    }, 3000); // 3 sekonda para se te filloje zhdukjeja
});


</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
const mapByCity = <?= json_encode($mapByCity, JSON_UNESCAPED_UNICODE) ?>;

const reportsMap = L.map('reports-map').setView([42.6629, 21.1655], 8);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18
}).addTo(reportsMap);

Object.values(mapByCity).forEach(city => {
    const marker = L.marker(city.coords).addTo(reportsMap);

    let popupHtml = `<strong>${city.label}</strong><br>${city.reports.length} raportim(e)<ul style="padding-left:16px;margin:6px 0;">`;
    city.reports.slice(0, 5).forEach(r => {
        popupHtml += `<li><a href="report_details.php?id=${r.id}">${r.type} - ${r.date}</a></li>`;
    });
    popupHtml += '</ul>';

    marker.bindPopup(popupHtml);
});
</script>
</body>
</html>
