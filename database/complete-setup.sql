-- ============================================
-- Restaurant System - Complete Database Setup
-- All-in-One SQL File with 100+ Authentic TN Foods
-- ============================================

-- Drop existing database if it exists
DROP DATABASE IF EXISTS restaurant_orders;

-- Create database
CREATE DATABASE restaurant_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_orders;

-- ============================================
-- CORE TABLES
-- ============================================

-- Restaurants table
CREATE TABLE restaurants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    primary_color VARCHAR(7) DEFAULT '#FF6B35',
    logo_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    level INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Permissions table
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Role permissions (many-to-many)
CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT DEFAULT 1,
    role_id INT NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT DEFAULT 1,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);

-- Menu items table
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT DEFAULT 1,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    image_url2 VARCHAR(255),
    image_url3 VARCHAR(255),
    image_url4 VARCHAR(255),
    image_url5 VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT DEFAULT 1,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    table_number VARCHAR(10) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('placed', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'placed',
    special_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_table (table_number)
);

-- Order items table (menu_item_id nullable + ON DELETE SET NULL so menu items can be deleted; order history kept via menu_item_name)
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NULL,
    menu_item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    item_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT DEFAULT 1,
    user_id INT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type ENUM('order_placed', 'order_updated', 'feedback_received', 'system', 'alert') DEFAULT 'system',
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    related_order_id INT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT DEFAULT 1,
    order_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    overall_rating INT NOT NULL CHECK (overall_rating BETWEEN 1 AND 5),
    food_quality INT CHECK (food_quality BETWEEN 1 AND 5),
    service_rating INT CHECK (service_rating BETWEEN 1 AND 5),
    ambience_rating INT CHECK (ambience_rating BETWEEN 1 AND 5),
    comments TEXT,
    admin_response TEXT,
    responded_by INT NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (responded_by) REFERENCES users(id)
);

-- Item ratings table
CREATE TABLE item_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    feedback_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- ============================================
-- ANALYTICS VIEWS
-- ============================================

CREATE OR REPLACE VIEW daily_sales AS
SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as total_orders,
    SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as revenue,
    AVG(CASE WHEN status != 'cancelled' THEN total_amount END) as avg_order_value
FROM orders
GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW popular_items AS
SELECT 
    mi.id,
    mi.name,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.price * oi.quantity) as revenue
FROM menu_items mi
LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
GROUP BY mi.id, mi.name
ORDER BY total_quantity DESC;

CREATE OR REPLACE VIEW peak_hours AS
SELECT 
    HOUR(created_at) as hour,
    COUNT(*) as order_count,
    SUM(total_amount) as revenue
FROM orders
WHERE status != 'cancelled'
GROUP BY HOUR(created_at)
ORDER BY hour;

-- ============================================
-- DATA INSERTION
-- ============================================

-- 1. Restaurant
INSERT INTO restaurants (name, slug, address, phone, email, primary_color) VALUES
('Obito Ani Foodzz', 'obito-ani-foodzz', '45 Temple Street, Gobichettipalayam, TN 625001', '+91-9876543210', 'contact@obito-tn.com', '#E65100');

-- 2. Roles (waiter = frontend/waiter/dashboard.php, login: waiter/login.php or admin/login.php)
INSERT INTO roles (name, display_name, level) VALUES
('super_admin', 'Super Administrator', 100),
('admin', 'Administrator', 90),
('manager', 'Manager', 70),
('waiter', 'Waiter', 50),
('chef', 'Chef', 40);

-- 3. Permissions
INSERT INTO permissions (name, description, module) VALUES
('users.view', 'View users', 'users'),
('users.create', 'Create new users', 'users'),
('users.edit', 'Edit user details', 'users'),
('users.delete', 'Delete users', 'users'),
('menu.view', 'View menu items', 'menu'),
('menu.edit', 'Edit menu items', 'menu'),
('orders.view', 'View orders', 'orders'),
('orders.manage', 'Manage order status', 'orders'),
('analytics.view', 'View analytics and reports', 'analytics'),
('reports.generate', 'Generate reports', 'reports'),
('feedback.view', 'View customer feedback', 'feedback'),
('feedback.respond', 'Respond to feedback', 'feedback'),
('restaurants.manage', 'Manage restaurants', 'restaurants');

-- 4. Role Permissions
INSERT INTO role_permissions (role_id, permission_id) SELECT 1, id FROM permissions;
INSERT INTO role_permissions (role_id, permission_id) SELECT 2, id FROM permissions WHERE name != 'restaurants.manage';
INSERT INTO role_permissions (role_id, permission_id) SELECT 3, id FROM permissions WHERE module IN ('analytics', 'reports', 'feedback', 'orders', 'menu');
INSERT INTO role_permissions (role_id, permission_id) SELECT 4, id FROM permissions WHERE module IN ('orders', 'menu');
INSERT INTO role_permissions (role_id, permission_id) SELECT 5, id FROM permissions WHERE name IN ('orders.view', 'orders.manage', 'menu.view');

-- 5. Users (role_id 4=waiter; demo: admin/admin123, manager/waiter/chef use password 'password')
INSERT INTO users (restaurant_id, role_id, username, password_hash, full_name, phone) VALUES
(1, 1, 'admin', '$2y$10$lbaCGgkY5u1vW.sUou4yv.apwKqlwa9aXjnhCc3WX1B8lN9Ag7tp2', 'Restaurant Admin', '+91-9999999999'),
(1, 3, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Branch Manager', '+91-8888888888'),
(1, 4, 'waiter', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ramu Waiter', '+91-7777777777'),
(1, 5, 'chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chef Murugan', '+91-6666666666');

-- 6. Categories (3 for test)
INSERT INTO categories (restaurant_id, name, description, display_order) VALUES
(1, 'Breakfast', 'Traditional Tiffin Varieties', 1),
(1, 'Lunch', 'Meals & Rice Varieties', 2),
(1, 'Beverages & Sweets', 'Drinks and Desserts', 3);

-- 7. Menu Items (10 items for test)
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 1, 'Idli (Set of 2)', 'Steamed fluffy rice cakes served with sambar and chutney', 40.00, 'uploads/food/idli.jpg', 1),
(1, 1, 'Plain Dosa', 'Crispy fermented crepe served with chutneys', 60.00, 'uploads/food/plain_dosa.jpg', 1),
(1, 1, 'Masala Dosa', 'Dosa stuffed with spiced potato masala', 80.00, 'uploads/food/masala_dosa.jpg', 1),
(1, 1, 'Medu Vada', 'Crispy lentil donut served with chutneys', 20.00, 'uploads/food/medu_vada.jpg', 1),
(1, 2, 'South Indian Full Meals', 'Rice, Sambar, Rasam, Kootu, Poriyal, Curd, Sweet', 150.00, 'uploads/food/full_meals.jpg', 1),
(1, 2, 'Chicken Biryani', 'Traditional Ambur style chicken biryani', 180.00, 'uploads/food/chicken_biryani.jpg', 1),
(1, 2, 'Parotta with Salna (Set of 2)', 'Flaky layered flatbread with spicy gravy', 50.00, 'uploads/food/parotta_salna.jpg', 1),
(1, 2, 'Chicken 65', 'Deep fried spicy chicken chunks', 160.00, 'uploads/food/chicken_65.jpg', 1),
(1, 3, 'Filter Coffee', 'Traditional South Indian frothy coffee', 30.00, 'uploads/food/filter_coffee.jpg', 1),
(1, 3, 'Gulab Jamun', 'Soft milk solids in sugar syrup', 40.00, 'uploads/food/gulab_jamun.jpg', 1);

-- 8. Sample Orders
INSERT INTO orders (restaurant_id, order_number, table_number, total_amount, status, created_at) VALUES
(1, 'ORD-2024-001', '5', 250.00, 'served', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'ORD-2024-002', '3', 520.00, 'served', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'ORD-2024-003', '7', 150.00, 'ready', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'ORD-2024-004', '2', 210.00, 'preparing', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

-- 9. Order Items
INSERT INTO order_items (order_id, menu_item_id, menu_item_name, quantity, price) VALUES
-- Order 1: Idli + Vada + Coffee
(1, 1, 'Idli (Set of 2)', 2, 40.00),
(1, 4, 'Medu Vada', 2, 20.00),
(1, 9, 'Filter Coffee', 2, 30.00),

-- Order 2: Biryani + Chicken 65
(2, 6, 'Chicken Biryani', 2, 180.00),
(2, 8, 'Chicken 65', 1, 160.00),

-- Order 3: Meals
(3, 5, 'South Indian Full Meals', 1, 150.00),

-- Order 4: Parotta + Salna + Vada
(4, 7, 'Parotta with Salna', 3, 50.00),
(4, 4, 'Medu Vada', 1, 20.00);

-- ============================================
-- DATABASE SETUP COMPLETE
-- ============================================

SELECT 'Database setup with 10 menu items completed successfully!' as Status;
SELECT 'Menu Items Count:' as Metric, COUNT(*) as Value FROM menu_items;
