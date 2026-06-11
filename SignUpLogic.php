<?php
session_start();
include_once('config.php');

if (isset($_POST['submit'])) {

    // Trim input-et
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Ruan inputet 
    $_SESSION['old'] = [
        'name'=> $name,
        'username'=> $username,
        'email'=> $email
    ];

    // e kontrollon nese ka input te zbrazet
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $_SESSION['error_message'] = "⚠️ Plotësoni të gjitha fushat.";
        header("Location: SignUp.php");
        exit;
    }

    // nese passwordet nuk perputhen
    if ($password !== $confirm) {
        $_SESSION['error_message'] = "⚠️ Fjalëkalimet nuk përputhen.";
        header("Location: SignUp.php");
        exit;
    }

    // Validimi i passwordit
    if (
        strlen($password) < 6 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W_]/', $password)
    ) {
        $_SESSION['error_message'] =
            "⚠️ Fjalëkalimi duhet të ketë minimum 6 karaktere, një shkronjë të madhe, një numër dhe një simbol.";
        header("Location: SignUp.php");
        exit;
    }

    // Email valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "⚠️ Email-i duhet të ketë format të saktë.";
        header("Location: SignUp.php");
        exit;
    }

    // Kontrollo username/email nese jane ekzistues
    $check = $conn->prepare(
        "SELECT id FROM users WHERE username = :username OR email = :email"
    );
    $check->execute([
        ':username' => $username,
        ':email'    => $email
    ]);

    if ($check->rowCount() > 0) {
        $_SESSION['error_message'] = "⚠️ Username ose email ekziston tashmë.";
        header("Location: SignUp.php");
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $is_admin = 0;

    // Foto profili default
    $profile_pic = 'uploads/member.png';
    // e kontrollon nese eshte derguar nje file dhe nuk ka gabim gjate ngarkimit
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/avif'];
                        // lista e tipeve te lejuara
                        // kontrollon nese tipi i fotos eshte ne listen e lejuar
        if (in_array($_FILES['profile_pic']['type'], $allowed_types)) {
            // marrim extension te file-it
            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);

            // e krijon nje emer unik per file-in per me u largu nga konfliktet dmth overwrite
            $filename = 'uploads/profile_' . time() . '.' . $ext;

            // e leviz file-in ne folderin e duhur
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filename);

            // e vendos path-in e fotos se re ne databaze
            $profile_pic = $filename;
        }
    }

    // Insert në DB
    $SignUpQuery = $conn->prepare("
        INSERT INTO users (name, email, username, password, is_admin, profile_pic)
        VALUES (:name, :email, :username, :password, :is_admin, :profile_pic)
    ");

    if ($SignUpQuery->execute([
        ':name'=> $name,
        ':email'=> $email,
        ':username'=> $username,
        ':password'=> $hashedPassword,
        ':is_admin'=> $is_admin,
        ':profile_pic'=> $profile_pic
    ])) {

        // Pastro input-et pas suksesit
        unset($_SESSION['old']);

        $_SESSION['success_message'] = "✅ Regjistrimi u krye me sukses!";
        header("Location: Login.php");
        exit;
    }

    // Nëse diçka dështon
    $_SESSION['error_message'] = "⚠️ Ndodhi një gabim gjatë regjistrimit.";
    header("Location: SignUp.php");
    exit;
}
?>
