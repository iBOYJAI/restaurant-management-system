<?php
// =================================================================
// PEXELS FOOD IMAGE DOWNLOADER - 10 Menu Items for Test
// =================================================================
// Usage: php backend/scripts/download_images.php

ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pexels API Key
define('PEXELS_API_KEY', 'Splj8ikSH1WsNvO8ffKaBzSeLCwOHgt9OBWu9O4n2ypepEFncKOyMcri');

$targetDir = __DIR__ . '/../../uploads/food/';

// Delete existing food folder and recreate for fresh download
if (file_exists($targetDir)) {
    array_map('unlink', glob($targetDir . '*'));
    rmdir($targetDir);
}
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// ------------------------------------------------------------------
// 10 Menu Items Only (matching complete-setup.sql seed)
// ------------------------------------------------------------------
$items = [
    'idli.jpg' => 'Idli',
    'plain_dosa.jpg' => 'Dosa',
    'masala_dosa.jpg' => 'Masala Dosa',
    'medu_vada.jpg' => 'Medu Vada',
    'full_meals.jpg' => 'South Indian Thali',
    'chicken_biryani.jpg' => 'Chicken Biryani',
    'parotta_salna.jpg' => 'Parotta Salna',
    'chicken_65.jpg' => 'Chicken 65',
    'filter_coffee.jpg' => 'Indian Filter Coffee',
    'gulab_jamun.jpg' => 'Gulab Jamun',
];

function logMsg($msg)
{
    echo "[" . date('H:i:s') . "] $msg\n";
}

function curlRequest($url, $headers = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $defaultHeaders = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ];
    if (!empty($headers)) {
        $defaultHeaders = array_merge($defaultHeaders, $headers);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($info['http_code'] >= 400 || !$result) {
        logMsg("       ! HTTP {$info['http_code']} | $error");
        return null;
    }
    return $result;
}

$usedPhotoIds = [];

function searchPexels($term, &$usedPhotoIds)
{
    $url = "https://api.pexels.com/v1/search?query=" . urlencode($term . " food") . "&per_page=40&orientation=landscape";
    $headers = ["Authorization: " . PEXELS_API_KEY];
    $json = curlRequest($url, $headers);

    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['photos'])) {
            $photos = $data['photos'];
            shuffle($photos);
            foreach ($photos as $photo) {
                $id = $photo['id'];
                if (!in_array($id, $usedPhotoIds)) {
                    $usedPhotoIds[] = $id;
                    return $photo['src']['large'] ?? $photo['src']['medium'] ?? $photo['src']['original'];
                }
            }
            if (!empty($photos)) {
                logMsg("   ! Warning: unique pool exhausted for '$term', reusing image.");
                return $photos[0]['src']['large'] ?? $photos[0]['src']['medium'] ?? null;
            }
        }
    }
    return null;
}

function getPlaceholder($term)
{
    return "https://placehold.co/1280x720/orange/white?text=" . urlencode($term);
}

logMsg("Starting Pexels Download - 10 Menu Items (Fresh)");

foreach ($items as $filename => $searchTerm) {
    $filepath = $targetDir . $filename;
    logMsg("Fetching: $searchTerm -> $filename");

    $imageUrl = searchPexels($searchTerm, $usedPhotoIds);
    $saved = false;

    if ($imageUrl) {
        logMsg("   > URL: $imageUrl");
        $content = curlRequest($imageUrl);
        if ($content && strlen($content) > 1000) {
            file_put_contents($filepath, $content);
            logMsg("   > SAVED (Pexels)");
            $saved = true;
        }
    }

    if (!$saved) {
        logMsg("   > Using Placeholder");
        $placeholderContent = curlRequest(getPlaceholder($searchTerm));
        if ($placeholderContent) {
            file_put_contents($filepath, $placeholderContent);
        }
    }

    usleep(200000); // 0.2s delay
}

logMsg("Batch Complete. 10 images saved to uploads/food/");
