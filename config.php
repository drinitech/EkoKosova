<?php
session_start();

date_default_timezone_set('Europe/Belgrade');

$user = "root";
$pass = "root";
$server = "localhost";
$dbname = "ekokosova";

try {
    $conn = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8",$user,$pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Gabim në lidhje me DB: " . $e->getMessage());
}
?>
