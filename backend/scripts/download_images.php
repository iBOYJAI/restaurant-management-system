<?php
// =================================================================
// PEXELS FOOD IMAGE DOWNLOADER (High Quality)
// =================================================================
// Usage: php backend/scripts/download_images.php

ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pexels API Key
define('PEXELS_API_KEY', 'Splj8ikSH1WsNvO8ffKaBzSeLCwOHgt9OBWu9O4n2ypepEFncKOyMcri');

$targetDir = __DIR__ . '/../../uploads/food/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// ------------------------------------------------------------------
// CONFIGURATION
// ------------------------------------------------------------------
$items = [
    'idli.jpg' => 'Idli',
    'sambar_idli.jpg' => 'Idli Sambar',
    'mini_idli.jpg' => 'Mini Idli',
    'kanchipuram_idli.jpg' => 'Kanchipuram Idli',
    'rava_idli.jpg' => 'Rava Idli',
    'plain_dosa.jpg' => 'Dosa',
    'masala_dosa.jpg' => 'Masala Dosa',
    'ghee_roast.jpg' => 'Ghee Roast Dosa',
    'onion_dosa.jpg' => 'Onion Dosa',
    'paper_roast.jpg' => 'Paper Dosa',
    'podi_dosa.jpg' => 'Podi Dosa',
    'kal_dosa.jpg' => 'Kal Dosa',
    'egg_dosa.jpg' => 'Egg Dosa',
    'rava_dosa.jpg' => 'Rava Dosa',
    'onion_rava_dosa.jpg' => 'Onion Rava Dosa',
    'ghee_rava_masala.jpg' => 'Rava Masala Dosa',
    'wheat_dosa.jpg' => 'Godhuma Dosa',
    'ragi_dosa.jpg' => 'Ragi Dosa',
    'uttapam.jpg' => 'Uttapam',
    'onion_uttapam.jpg' => 'Onion Uttapam',
    'tomato_uttapam.jpg' => 'Tomato Uttapam',
    'mixed_veg_uttapam.jpg' => 'Vegetable Uttapam',
    'medu_vada.jpg' => 'Medu Vada',
    'sambar_vada.jpg' => 'Sambar Vada',
    'curd_vada.jpg' => 'Dahi Vada',
    'masala_vada.jpg' => 'Masala Vada',
    'ven_pongal.jpg' => 'Ven Pongal',
    'rava_pongal.jpg' => 'Rava Pongal',
    'poori_masala.jpg' => 'Puri Bhaji',
    'appam.jpg' => 'Appam',
    'idiyappam.jpg' => 'Idiyappam',
    'adai_avial.jpg' => 'Adai Dosa',
    'rava_kichadi.jpg' => 'Rava Kichadi',
    'semiya_upma.jpg' => 'Semiya Upma',
    'full_meals.jpg' => 'South Indian Thali',
    'sambar_sadam.jpg' => 'Sambar Rice',
    'curd_rice.jpg' => 'Curd Rice',
    'lemon_rice.jpg' => 'Lemon Rice',
    'tomato_rice.jpg' => 'Tomato Rice',
    'puliyodarai.jpg' => 'Puliyodarai',
    'coconut_rice.jpg' => 'Coconut Rice',
    'bisibelebath.jpg' => 'Bisi Bele Bath',
    'veg_biryani.jpg' => 'Vegetable Biryani',
    'mushroom_biryani.jpg' => 'Mushroom Biryani',
    'paneer_biryani.jpg' => 'Paneer Biryani',
    'chicken_biryani.jpg' => 'Chicken Biryani',
    'mutton_biryani.jpg' => 'Mutton Biryani',
    'egg_biryani.jpg' => 'Egg Biryani',
    'kuska.jpg' => 'Kuska Biryani',
    'dindigul_biryani.jpg' => 'Thalappakatti Biryani',
    'chapati_kurma.jpg' => 'Chapati',
    'parotta_salna.jpg' => 'Parotta Salna',
    'cola_urundai.jpg' => 'Kola Urundai',
    'meen_kuzhambu.jpg' => 'Fish Curry',
    'chicken_chettinad.jpg' => 'Chettinad Chicken',
    'prawn_thokku.jpg' => 'Prawn Masala',
    'kothu_parotta_veg.jpg' => 'Kothu Parotta',
    'egg_kothu.jpg' => 'Kothu Parotta',
    'chicken_kothu.jpg' => 'Chicken Kothu Parotta',
    'chilli_parotta.jpg' => 'Chilli Parotta',
    'bun_parotta.jpg' => 'Bun Parotta',
    'veechu_parotta.jpg' => 'Veechu Parotta',
    'ceylon_parotta.jpg' => 'Ceylon Parotta',
    'egg_veechu.jpg' => 'Egg Veechu Parotta',
    'kari_dosa.jpg' => 'Kari Dosa',
    'chicken_65.jpg' => 'Chicken 65',
    'mutton_chukka.jpg' => 'Mutton Chukka',
    'pallipalayam.jpg' => 'Pallipalayam Chicken',
    'pichu_potta.jpg' => 'Pichu Potta Kozhi',
    'kaadai_fry.jpg' => 'Quail Fry',
    'nethili_fry.jpg' => 'Fried Nethili',
    'vanjaram_fry.jpg' => 'Vanjaram Fry',
    'crab_masala.jpg' => 'Crab Masala',
    'pepper_chicken.jpg' => 'Pepper Chicken',
    'onion_bajji.jpg' => 'Onion Bajji',
    'banana_bajji.jpg' => 'Mirchi Bajji',
    'chilli_bajji.jpg' => 'Mirchi Bajji',
    'potato_bonda.jpg' => 'Aloo Bonda',
    'mysore_bonda.jpg' => 'Mysore Bonda',
    'keerai_vada.jpg' => 'Keerai Vada',
    'vazhaipoo_vada.jpg' => 'Vazhaipoo Vadai',
    'sundal.jpg' => 'Sundal',
    'pattani_sundal.jpg' => 'Pattani Sundal',
    'sweet_paniyaram.jpg' => 'Paddu',
    'kara_paniyaram.jpg' => 'Paddu',
    'murukku.jpg' => 'Murukku',
    'seedai.jpg' => 'Seedai',
    'thattai.jpg' => 'Thattai',
    'ribbon_pakoda.jpg' => 'Ribbon Pakoda',
    'karasev.jpg' => 'Kara Sev',
    'gobi_65.jpg' => 'Gobi Manchurian',
    'mushroom_65.jpg' => 'Mushroom Manchurian',
    'babycorn_manchurian.jpg' => 'Baby Corn Manchurian',
    'kesari.jpg' => 'Kesari Bat',
    'gulab_jamun.jpg' => 'Gulab Jamun',
    'mysore_pak.jpg' => 'Mysore Pak',
    'ghee_mysore_pak.jpg' => 'Mysore Pak',
    'jangiri.jpg' => 'Imarti',
    'palkova.jpg' => 'Khoa',
    'tirunelveli_halwa.jpg' => 'Halwa',
    'sakkarai_pongal.jpg' => 'Sweet Pongal',
    'payasam.jpg' => 'Kheer',
    'paruppu_payasam.jpg' => 'Pradhaman',
    'adhirasam.jpg' => 'Adhirasam',
    'ladoo.jpg' => 'Laddu',
    'badusha.jpg' => 'Balushahi',
    'rasmalai.jpg' => 'Ras Malai',
    'filter_coffee.jpg' => 'Indian Filter Coffee',
    'masala_tea.jpg' => 'Masala Chai',
    'sukku_coffee.jpg' => 'Coffee',
    'badam_milk.jpg' => 'Badam Milk',
    'rose_milk.jpg' => 'Rose Milk',
    'nannari.jpg' => 'Sarsaparilla drink',
    'jigarthanda.jpg' => 'Jigarthanda',
    'buttermilk.jpg' => 'Chaas',
    'lassi.jpg' => 'Lassi',
    'mango_lassi.jpg' => 'Mango Lassi',
    'lime_soda.jpg' => 'Lime Soda',
    'bovonto.jpg' => 'Cola drink',
    'paneer_soda.jpg' => 'Bottle soda'
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

    // Default Headers for Browser Mimicry + API Key
    $defaultHeaders = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
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

$usedPhotoIds = []; // Track used IDs to ensure global uniqueness

function searchPexels($term, &$usedPhotoIds)
{
    // Fetch large pool (40) to find unique images even for similar items (e.g. 5 types of Idli)
    $url = "https://api.pexels.com/v1/search?query=" . urlencode($term . " food") . "&per_page=40&orientation=landscape";

    $headers = [
        "Authorization: " . PEXELS_API_KEY
    ];

    $json = curlRequest($url, $headers);

    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['photos'])) {
            // Shuffle to randomize, but iterate to find an UNUSED one
            $photos = $data['photos'];
            shuffle($photos);

            foreach ($photos as $photo) {
                $id = $photo['id'];
                if (!in_array($id, $usedPhotoIds)) {
                    // Found a unique image!
                    $usedPhotoIds[] = $id;
                    return $photo['src']['large'] ?? $photo['src']['medium'] ?? $photo['src']['original'];
                }
            }

            // Fallback: If all 40 are used (rare), just pick the first random one
            if (!empty($photos)) {
                logMsg("   ! Warning: unique pool exhausted for '$term', reusing image.");
                return $photos[0]['src']['large'] ?? $photos[0]['src']['medium'];
            }
        }
    }
    return null;
}

function getPlaceholder($term)
{
    return "https://placehold.co/1280x720/orange/white?text=" . urlencode($term);
}

logMsg("Starting Pexels Download Batch (Mode: Unique & Fresh)...");

foreach ($items as $filename => $searchTerm) {
    $filepath = $targetDir . $filename;

    // Check exist - SKIP if exists to save API quota and time
    if (file_exists($filepath) && filesize($filepath) > 5000) {
        continue;
    }

    logMsg("Fetching: $searchTerm -> $filename");

    // 1. Try Pexels with tracking
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

    // 2. Fallback to Placeholder if failed
    if (!$saved) {
        logMsg("   > FAILED / Not Found. Using Placeholder.");
        file_put_contents($filepath, curlRequest(getPlaceholder($searchTerm)));
    }

    // Increase delay slightly to handle larger queries safely
    usleep(200000); // 0.2s
}

logMsg("Batch Complete.");
