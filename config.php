<?php
date_default_timezone_set('Europe/Belgrade');

// Lexon nje variabel ambienti nga cilido burim qe e ekspozon (getenv,
// $_ENV ose $_SERVER - runtime-t serverless nuk jane te gjitha njesoj).
function env($key, $default = null)
{
    $value = getenv($key);
    if ($value !== false && $value !== '') return $value;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

$server = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'ekokosova');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', 'root');

try {
    $conn = new PDO("mysql:host=$server;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Gabim në lidhje me DB: " . $e->getMessage());
}

// Ne Vercel, sesionet ruhen ne DB sepse funksionet serverless nuk kane
// disk te perbashket mes kerkesave (ndryshe nga MAMP lokalisht).
if (env('VERCEL')) {
    require_once __DIR__ . '/session_handler.php';
    session_set_save_handler(new PdoSessionHandler($conn), true);
}

session_start();
?>
