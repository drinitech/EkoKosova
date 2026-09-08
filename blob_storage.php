<?php
// Ngarkimi/fshirja e fotove.
// Lokalisht (MAMP): shkruan te folderi uploads/, si me pare.
// Ne Vercel: shkruan te Vercel Blob (BLOB_READ_WRITE_TOKEN), sepse
// disku i funksioneve serverless nuk ruhet mes kerkesave.

function blob_configured()
{
    return (bool) env('BLOB_READ_WRITE_TOKEN');
}

function blob_upload_file($tmpPath, $filename)
{
    $token = env('BLOB_READ_WRITE_TOKEN');
    $data = file_get_contents($tmpPath);
    if ($data === false) return false;

    $ch = curl_init("https://blob.vercel-storage.com/" . rawurlencode($filename));
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "x-api-version: 7",
            "x-content-type: " . (mime_content_type($tmpPath) ?: 'application/octet-stream'),
            "x-add-random-suffix: 1",
        ],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200 || !$response) return false;
    $json = json_decode($response, true);
    return $json['url'] ?? false;
}

function blob_delete_file($url)
{
    $token = env('BLOB_READ_WRITE_TOKEN');
    if (!$token || !$url) return;

    $ch = curl_init("https://blob.vercel-storage.com/delete");
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['urls' => [$url]]),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "x-api-version: 7",
        ],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// Ruan nje skedar te ngarkuar (nga $_FILES[...]['tmp_name']) dhe kthen
// vleren qe duhet ruajtur ne databaze (URL e plote nga Blob, ose "uploads/xxx" lokalisht).
// Kthen false nese ngarkimi deshton.
function save_uploaded_file($tmpPath, $filename)
{
    if (blob_configured()) {
        return blob_upload_file($tmpPath, $filename);
    }

    if (!is_dir(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0777, true);
    }

    $target = __DIR__ . '/uploads/' . $filename;
    return move_uploaded_file($tmpPath, $target) ? 'uploads/' . $filename : false;
}

// Fshin nje foto te ruajtur me pare (URL Blob ose rruge lokale "uploads/xxx").
function delete_uploaded_file($stored)
{
    if (!$stored || $stored === 'uploads/member.png') return;

    if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
        blob_delete_file($stored);
        return;
    }

    $bare = str_starts_with($stored, 'uploads/') ? substr($stored, strlen('uploads/')) : $stored;
    $path = __DIR__ . '/uploads/' . $bare;
    if (file_exists($path)) unlink($path);
}

// Kthen URL-ne per <img src="...">, qofte URL e plote (Blob) ose rruge lokale.
// I mbeshtet edhe rreshtat e vjeter qe ruajten vetem emrin e skedarit (pa "uploads/").
function resolve_upload_url($stored, $fallback = 'uploads/member.png')
{
    if (!$stored) return $fallback;
    if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) return $stored;
    if (str_starts_with($stored, 'uploads/') || str_starts_with($stored, 'img/')) return $stored;
    return 'uploads/' . $stored;
}
