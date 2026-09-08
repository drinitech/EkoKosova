<?php
$password_plain = "admin123"; 

$hash = password_hash($password_plain, PASSWORD_DEFAULT);

echo "Ky eshte hash-i qe duhet te vendosesh ne databaze: <br>";
echo $hash;
?>
