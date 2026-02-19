<?php

/**
 * Database Setup Script
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

header('Content-Type: text/plain');

try {
    // Connect without database selected
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server successfully.\n";

    // Read SQL file
    $sqlFile = __DIR__ . '/../database/complete-setup.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Remove comments to avoid issues with some parsers, 
    // but PDO can handle multiple queries if configured/supported.
    // simpler to just split by semicolon if needed, but let's try direct execution first.

    echo "Executing SQL setup...\n";

    // Execute the raw SQL
    $pdo->exec($sql);

    echo "Database setup completed successfully!\n";
    echo "Database 'restaurant_orders' created and populated.\n";

    // Images are already populated in uploads/food/
    // We do not need to trigger download_images.php automatically anymore.
    echo "Using existing images in uploads/food/ (No download triggered).\n";

    // Verify Links
    echo "\n------------------------------------------------\n";
    echo "Verifying Database Image Links...\n";
    echo "------------------------------------------------\n";

    $stmt = $pdo->query("SELECT id, name, image_url FROM menu_items WHERE image_url IS NOT NULL");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $linkedCount = 0;
    $missingCount = 0;

    foreach ($items as $item) {
        $imgPath = __DIR__ . '/../' . $item['image_url'];
        if (file_exists($imgPath)) {
            $linkedCount++;
        } else {
            $missingCount++;
            echo " [MISSING] {$item['name']} -> {$item['image_url']}\n";
        }
    }

    echo "Verification Complete: $linkedCount linked, $missingCount missing.\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
