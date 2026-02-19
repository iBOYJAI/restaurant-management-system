<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Reset Admin Password
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Generated Hash: " . $hash . "<br>";

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    echo "Admin password reset to 'admin123' successfully.<br>";

    // 2. Update Restaurant Name
    $newName = 'Obito Ani Foodzz';
    $stmt = $pdo->prepare("UPDATE restaurants SET name = ? WHERE id = 1");
    $stmt->execute([$newName]);
    echo "Restaurant ID 1 updated to '$newName'.<br>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
