<?php
/**
 * Migration: Allow menu item deletion when referenced in orders
 * Run: php database/migrate_allow_menu_delete.php
 */
require_once __DIR__ . '/../backend/config/database.php';

try {
    // Find FK constraint name for order_items -> menu_items
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = 'restaurant_orders' AND TABLE_NAME = 'order_items' AND REFERENCED_TABLE_NAME = 'menu_items'
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $fkName = $row['CONSTRAINT_NAME'] ?? null;

    if (!$fkName) {
        echo "No menu_item_id FK found - schema may already be updated.\n";
        exit(0);
    }

    echo "Dropping FK: $fkName\n";
    $pdo->exec("ALTER TABLE order_items DROP FOREIGN KEY `$fkName`");

    echo "Making menu_item_id nullable...\n";
    $pdo->exec("ALTER TABLE order_items MODIFY menu_item_id INT NULL");

    echo "Adding FK with ON DELETE SET NULL...\n";
    $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT order_items_menu_item_fk FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL");

    echo "Migration completed. Menu items can now be deleted.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
