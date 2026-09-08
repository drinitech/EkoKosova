<?php
// Front controller i vetem per Vercel: Hobby plan lejon max 12 Serverless
// Functions per deployment, ndersa kjo faqe ka ~25 skedare .php. Ne vend
// qe secili skedar te behet nje function me vete, gjithcka kalon nga ky
// router (shih vercel.json), i cili thjesht kerkon skedarin e duhur dhe
// e ekzekuton, si te ishte kerkuar direkt.

chdir(__DIR__);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$name = basename($path);

if ($name === '' || $path === '/') {
    $name = 'index.php';
}

if (!preg_match('/^[A-Za-z0-9_]+\.php$/', $name)) {
    http_response_code(404);
    echo "404 - Faqja nuk u gjet.";
    exit;
}

$file = __DIR__ . '/' . $name;

if (!is_file($file)) {
    http_response_code(404);
    echo "404 - Faqja nuk u gjet.";
    exit;
}

require $file;
