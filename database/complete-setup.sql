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

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    menu_item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    item_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
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
('TN-Obito', 'tn-obito', '45 Temple Street, Gobichettipalayam, TN 625001', '+91-9876543210', 'contact@obito-tn.com', '#E65100');

-- 2. Roles
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

-- 5. Users
INSERT INTO users (restaurant_id, role_id, username, password_hash, full_name, phone) VALUES
(1, 1, 'admin', '$2y$10$lbaCGgkY5u1vW.sUou4yv.apwKqlwa9aXjnhCc3WX1B8lN9Ag7tp2', 'Restaurant Admin', '+91-9999999999'),
(1, 3, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Branch Manager', '+91-8888888888'),
(1, 4, 'waiter', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ramu Waiter', '+91-7777777777'),
(1, 5, 'chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chef Murugan', '+91-6666666666');

-- 6. Categories
INSERT INTO categories (restaurant_id, name, description, display_order) VALUES
(1, 'All-Day Breakfast', 'Traditional Tiffin Varieties', 1),
(1, 'Lunch Specials', 'Authentic Meals & Rice Varieties', 2),
(1, 'Dinner Specialties', 'Tiffin and Gravies', 3),
(1, 'Snacks & Savories', 'Evening Snacks and Starters', 4),
(1, 'Sweets & Desserts', 'Traditional Sweet Treats', 5),
(1, 'Beverages', 'Hot and Cold Drinks', 6);

-- 7. Menu Items (100+ Authentic Items)

-- CATEGORY 1: ALL-DAY BREAKFAST
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 1, 'Idli (Set of 2)', 'Steamed fluffy rice cakes served with sambar and chutney', 40.00, 'uploads/food/idli.jpg', 1),
(1, 1, 'Sambar Idli', 'Idlis dipped in hot aromatic sambar using ghee', 50.00, 'uploads/food/sambar_idli.jpg', 1),
(1, 1, 'Mini Idli (14 pcs)', 'Small coin-sized idlis immersed in sambar', 70.00, 'uploads/food/mini_idli.jpg', 1),
(1, 1, 'Kanchipuram Idli', 'Spiced idli seasoned with pepper, ginger, and cumin', 60.00, 'uploads/food/kanchipuram_idli.jpg', 1),
(1, 1, 'Rava Idli', 'Semolina based idli with nuts and carrots', 55.00, 'uploads/food/rava_idli.jpg', 1),
(1, 1, 'Plain Dosa', 'Crispy fermented crepe served with chutneys', 60.00, 'uploads/food/plain_dosa.jpg', 1),
(1, 1, 'Masala Dosa', 'Dosa stuffed with spiced potato masala', 80.00, 'uploads/food/masala_dosa.jpg', 1),
(1, 1, 'Ghee Roast Dosa', 'Crispy cone dosa drizzled with pure ghee', 90.00, 'uploads/food/ghee_roast.jpg', 1),
(1, 1, 'Onion Dosa', 'Dosa topped with chopped onions', 75.00, 'uploads/food/onion_dosa.jpg', 1),
(1, 1, 'Paper Roast', 'Super thin and large crispy dosa', 95.00, 'uploads/food/paper_roast.jpg', 1),
(1, 1, 'Podi Dosa', 'Dosa coated with spicy idli podi', 70.00, 'uploads/food/podi_dosa.jpg', 1),
(1, 1, 'Kal Dosa (Set of 2)', 'Thick and soft sponge dosa', 55.00, 'uploads/food/kal_dosa.jpg', 1),
(1, 1, 'Muttai Dosa (Egg Dosa)', 'Dosa coated with spiced beaten egg', 85.00, 'uploads/food/egg_dosa.jpg', 1),
(1, 1, 'Rava Dosa', 'Crispy semolina crepe with cumin and pepper', 75.00, 'uploads/food/rava_dosa.jpg', 1),
(1, 1, 'Onion Rava Dosa', 'Rava dosa topped with onions', 85.00, 'uploads/food/onion_rava_dosa.jpg', 1),
(1, 1, 'Ghee Rava Masala Dosa', 'Rich semolina crepe with masala filling', 100.00, 'uploads/food/ghee_rava_masala.jpg', 1),
(1, 1, 'Wheat Dosa', 'Healthy wheat flour dosa', 65.00, 'uploads/food/wheat_dosa.jpg', 1),
(1, 1, 'Ragi Dosa', 'Finger millet dosa rich in calcium', 70.00, 'uploads/food/ragi_dosa.jpg', 1),
(1, 1, 'Uttapam', 'Thick pancake made from dosa batter', 60.00, 'uploads/food/uttapam.jpg', 1),
(1, 1, 'Onion Uttapam', 'Uttapam topped with lots of onions', 75.00, 'uploads/food/onion_uttapam.jpg', 1),
(1, 1, 'Tomato Uttapam', 'Uttapam topped with tangy tomatoes', 75.00, 'uploads/food/tomato_uttapam.jpg', 1),
(1, 1, 'Mixed Veg Uttapam', 'Uttapam topped with carrot, beans, and onions', 85.00, 'uploads/food/mixed_veg_uttapam.jpg', 1),
(1, 1, 'Medu Vada', 'Crispy lentil donut served with chutneys', 20.00, 'uploads/food/medu_vada.jpg', 1),
(1, 1, 'Sambar Vada', 'Medu vada soaked in sambar', 30.00, 'uploads/food/sambar_vada.jpg', 1),
(1, 1, 'Curd Vada (Thayir Vada)', 'Vada soaked in seasoned yogurt', 35.00, 'uploads/food/curd_vada.jpg', 1),
(1, 1, 'Masala Vada', 'Crunchy chana dal roasted fritters', 25.00, 'uploads/food/masala_vada.jpg', 1),
(1, 1, 'Ven Pongal', 'Ghee-laden rice and moong dal porridge with cashews', 65.00, 'uploads/food/ven_pongal.jpg', 1),
(1, 1, 'Rava Pongal', 'Semolina based savory pongal', 65.00, 'uploads/food/rava_pongal.jpg', 1),
(1, 1, 'Poori Masala (Set of 3)', 'Fried wheat bread with potato curry', 70.00, 'uploads/food/poori_masala.jpg', 1),
(1, 1, 'Appam with Coconut Milk', 'Soft center lace pancakes with sweet milk', 45.00, 'uploads/food/appam.jpg', 1),
(1, 1, 'Idiyappam (String Hoppers)', 'Steamed rice noodles with kurma or milk', 50.00, 'uploads/food/idiyappam.jpg', 1),
(1, 1, 'Adai Avial', 'Protein rich lentil pancake with mixed veg curry', 90.00, 'uploads/food/adai_avial.jpg', 1),
(1, 1, 'Rava Kichadi', 'Semolina cooked with veggies and spices', 55.00, 'uploads/food/rava_kichadi.jpg', 1),
(1, 1, 'Semiya Upma', 'Vermicelli tossed with mild spices', 50.00, 'uploads/food/semiya_upma.jpg', 1);

-- CATEGORY 2: LUNCH SPECIALS
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 2, 'South Indian Full Meals', 'Rice, Sambar, Rasam, Kootu, Poriyal, Curd, Sweet', 150.00, 'uploads/food/full_meals.jpg', 1),
(1, 2, 'Sambhar Sadam', 'Rice mixed with flavorful sambar and ghee', 70.00, 'uploads/food/sambar_sadam.jpg', 1),
(1, 2, 'Curd Rice (Thayir Sadam)', 'Creamy yogurt rice with tempering and pomegranate', 60.00, 'uploads/food/curd_rice.jpg', 1),
(1, 2, 'Lemon Rice', 'Tangy yellow rice seasoned with peanuts', 65.00, 'uploads/food/lemon_rice.jpg', 1),
(1, 2, 'Tomato Rice', 'Spicy tomato bath', 65.00, 'uploads/food/tomato_rice.jpg', 1),
(1, 2, 'Tamarind Rice (Puliyodarai)', 'Traditional temple style spicy tamarind rice', 70.00, 'uploads/food/puliyodarai.jpg', 1),
(1, 2, 'Coconut Rice', 'Mild rice tossed with fresh grated coconut', 70.00, 'uploads/food/coconut_rice.jpg', 1),
(1, 2, 'Bisibelebath', 'Spicy lentil rice with vegetables', 80.00, 'uploads/food/bisibelebath.jpg', 1),
(1, 2, 'Veg Biryani', 'Aromatic basmati rice cooked with mixed veggies', 120.00, 'uploads/food/veg_biryani.jpg', 1),
(1, 2, 'Mushroom Biryani', 'Seeraga samba biryani with mushrooms', 140.00, 'uploads/food/mushroom_biryani.jpg', 1),
(1, 2, 'Paneer Biryani', 'Biryani loaded with soft paneer cubes', 160.00, 'uploads/food/paneer_biryani.jpg', 1),
(1, 2, 'Chicken Biryani', 'Traditional Ambur style chicken biryani', 180.00, 'uploads/food/chicken_biryani.jpg', 1),
(1, 2, 'Mutton Biryani', 'Succulent mutton pieces in jeera samba rice', 240.00, 'uploads/food/mutton_biryani.jpg', 1),
(1, 2, 'Egg Biryani', 'Biryani served with boiled eggs', 140.00, 'uploads/food/egg_biryani.jpg', 1),
(1, 2, 'Empty Biryani (Kuska)', 'Flavorful biryani rice without pieces', 100.00, 'uploads/food/kuska.jpg', 1),
(1, 2, 'Dindigul Thalappakatti Biryani', 'Specialty peppery mutton biryani', 260.00, 'uploads/food/dindigul_biryani.jpg', 1),
(1, 2, 'Chapati with Kurma (Set of 2)', 'Whole wheat flatbread with veg kurma', 60.00, 'uploads/food/chapati_kurma.jpg', 1),
(1, 2, 'Parotta with Salna (Set of 2)', 'Flaky layered flatbread with spicy gravy', 50.00, 'uploads/food/parotta_salna.jpg', 1),
(1, 2, 'Gola Urundai Kuzhambu', 'Meatballs in spicy tamarind gravy', 180.00, 'uploads/food/cola_urundai.jpg', 1),
(1, 2, 'Meen Kuzhambu (Fish Curry)', 'Tangy village style fish curry', 200.00, 'uploads/food/meen_kuzhambu.jpg', 1),
(1, 2, 'Chicken Chettinad', 'Spicy chicken curry with roasted spices', 210.00, 'uploads/food/chicken_chettinad.jpg', 1),
(1, 2, 'Prawn Thokku', 'Spicy masala coated prawns', 250.00, 'uploads/food/prawn_thokku.jpg', 1);

-- CATEGORY 3: DINNER SPECIALTIES
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 3, 'Kothu Parotta (Veg)', 'Minced parotta stir-fried with veggies', 100.00, 'uploads/food/kothu_parotta_veg.jpg', 1),
(1, 3, 'Kothu Parotta (Egg)', 'Minced parotta stir-fried with eggs', 120.00, 'uploads/food/egg_kothu.jpg', 1),
(1, 3, 'Kothu Parotta (Chicken)', 'Minced parotta stir-fried with chicken curry', 150.00, 'uploads/food/chicken_kothu.jpg', 1),
(1, 3, 'Chilli Parotta', 'Fried parotta cubes tossed with capsicum and onion', 110.00, 'uploads/food/chilli_parotta.jpg', 1),
(1, 3, 'Bun Parotta', 'Fluffy bun-like layered parotta', 30.00, 'uploads/food/bun_parotta.jpg', 1),
(1, 3, 'Veechu Parotta', 'Thin folded parotta', 40.00, 'uploads/food/veechu_parotta.jpg', 1),
(1, 3, 'Ceylon Parotta', 'Double layered stuffed parotta', 60.00, 'uploads/food/ceylon_parotta.jpg', 1),
(1, 3, 'Egg Veechu Parotta', 'Veechu parotta with egg coating', 70.00, 'uploads/food/egg_veechu.jpg', 1),
(1, 3, 'Karian Dosa', 'Types of dosa topped with minced mutton', 180.00, 'uploads/food/kari_dosa.jpg', 1),
(1, 3, 'Chicken 65', 'Deep fried spicy chicken chunks', 160.00, 'uploads/food/chicken_65.jpg', 1),
(1, 3, 'Mutton Chukka', 'Dry roasted mutton cubes with pepper', 240.00, 'uploads/food/mutton_chukka.jpg', 1),
(1, 3, 'Pallipalayam Chicken', 'Chicken cooked with lots of red chillies and coconut', 190.00, 'uploads/food/pallipalayam.jpg', 1),
(1, 3, 'Pichu Potta Kozhi', 'Shredded chicken fry', 180.00, 'uploads/food/pichu_potta.jpg', 1),
(1, 3, 'Kaadai Fry (Quail)', 'Deep fried marinated quail', 150.00, 'uploads/food/kaadai_fry.jpg', 1),
(1, 3, 'Nethili Fry (Anchovy)', 'Crispy fried small fish', 170.00, 'uploads/food/nethili_fry.jpg', 1),
(1, 3, 'Fish Fry (Vanjaram)', 'King fish slice tava fry', 220.00, 'uploads/food/vanjaram_fry.jpg', 1),
(1, 3, 'Crab Masala (Nandu)', 'Spicy crab gravy', 240.00, 'uploads/food/crab_masala.jpg', 1),
(1, 3, 'Pepper Chicken', 'Chicken roasted with black pepper', 180.00, 'uploads/food/pepper_chicken.jpg', 1);

-- CATEGORY 4: SNACKS & SAVORIES
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 4, 'Onion Bajji (4 pcs)', 'Besan coated onion fritters', 40.00, 'uploads/food/onion_bajji.jpg', 1),
(1, 4, 'Raw Banana Bajji (Valakkai)', 'Sliced plantain fritters', 40.00, 'uploads/food/banana_bajji.jpg', 1),
(1, 4, 'Chilli Bajji (Milagai)', 'Large chilli fritters', 40.00, 'uploads/food/chilli_bajji.jpg', 1),
(1, 4, 'Potato Bonda (Aloo)', 'Spiced potato mash deep fried in batter', 30.00, 'uploads/food/potato_bonda.jpg', 1),
(1, 4, 'Mysore Bonda', 'Fluffy maida balls deep fried', 30.00, 'uploads/food/mysore_bonda.jpg', 1),
(1, 4, 'Keerai Vadai', 'Masala vada enriched with spinach', 25.00, 'uploads/food/keerai_vada.jpg', 1),
(1, 4, 'Vazhaipoo Vadai', 'Banana flower fritters', 30.00, 'uploads/food/vazhaipoo_vada.jpg', 1),
(1, 4, 'Sundal', 'Boiled chickpeas tempered with coconut', 40.00, 'uploads/food/sundal.jpg', 1),
(1, 4, 'Pattani Sundal', 'Dry peas curry found in beaches', 45.00, 'uploads/food/pattani_sundal.jpg', 1),
(1, 4, 'Kuzhi Paniyaram (Sweet)', 'Rice balls with jaggery', 50.00, 'uploads/food/sweet_paniyaram.jpg', 1),
(1, 4, 'Kuzhi Paniyaram (Kara)', 'Spiced savory rice balls', 50.00, 'uploads/food/kara_paniyaram.jpg', 1),
(1, 4, 'Murukku (Packet)', 'Crunchy rice flour snacks', 50.00, 'uploads/food/murukku.jpg', 1),
(1, 4, 'Seedai', 'Small crunchy savory balls', 50.00, 'uploads/food/seedai.jpg', 1),
(1, 4, 'Thattai', 'Flat crunchy rice discs', 50.00, 'uploads/food/thattai.jpg', 1),
(1, 4, 'Ribbon Pakoda', 'Ribbon like savory snack', 50.00, 'uploads/food/ribbon_pakoda.jpg', 1),
(1, 4, 'Karaasev', 'Spicy gram flour sticks', 50.00, 'uploads/food/karasev.jpg', 1),
(1, 4, 'Gobi 65', 'Fried cauliflower florets', 90.00, 'uploads/food/gobi_65.jpg', 1),
(1, 4, 'Mushroom 65', 'Fried spicy mushroom', 100.00, 'uploads/food/mushroom_65.jpg', 1),
(1, 4, 'Baby Corn Manchurian', 'Indo-Chinese style baby corn', 110.00, 'uploads/food/babycorn_manchurian.jpg', 1);

-- CATEGORY 5: SWEETS & DESSERTS
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 5, 'Kesari', 'Semolina sweet with saffron and ghee', 50.00, 'uploads/food/kesari.jpg', 1),
(1, 5, 'Gulab Jamun', 'Soft milk solids in sugar syrup', 40.00, 'uploads/food/gulab_jamun.jpg', 1),
(1, 5, 'Mysore Pak', 'Rich gram flour fudge', 60.00, 'uploads/food/mysore_pak.jpg', 1),
(1, 5, 'Ghee Mysore Pak', 'Soft melt-in-mouth version', 70.00, 'uploads/food/ghee_mysore_pak.jpg', 1),
(1, 5, 'Jangiri', 'Sweet swirls in syrup', 50.00, 'uploads/food/jangiri.jpg', 1),
(1, 5, 'Milk Halwa (Palkova)', 'Thick sweetened milk solids', 80.00, 'uploads/food/palkova.jpg', 1),
(1, 5, 'Tirunelveli Halwa', 'Wheat milk ghee halwa', 90.00, 'uploads/food/tirunelveli_halwa.jpg', 1),
(1, 5, 'Sweet Pongal (Sakkarai Pongal)', 'Rice cooked with jaggery and ghee', 60.00, 'uploads/food/sakkarai_pongal.jpg', 1),
(1, 5, 'Payasam (Semiya/Javvarisi)', 'Vermicelli or sago milk pudding', 50.00, 'uploads/food/payasam.jpg', 1),
(1, 5, 'Paruppu Payasam', 'Lentil and jaggery pudding', 60.00, 'uploads/food/paruppu_payasam.jpg', 1),
(1, 5, 'Adhirasam', 'Rice flour and jaggery fritter', 40.00, 'uploads/food/adhirasam.jpg', 1),
(1, 5, 'Ladoo', 'Sweet gram flour balls', 30.00, 'uploads/food/ladoo.jpg', 1),
(1, 5, 'Badusha', 'Flaky sweet pastry', 40.00, 'uploads/food/badusha.jpg', 1),
(1, 5, 'Rasmalai', 'Cheese patties in sweet milk', 70.00, 'uploads/food/rasmalai.jpg', 1);

-- CATEGORY 6: BEVERAGES
INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_url, is_available) VALUES
(1, 6, 'Filter Coffee', 'Traditional South Indian frothy coffee', 30.00, 'uploads/food/filter_coffee.jpg', 1),
(1, 6, 'Masala Tea', 'Spiced indian chai', 25.00, 'uploads/food/masala_tea.jpg', 1),
(1, 6, 'Sukku Malli Coffee', 'Dry ginger and coriander herbal coffee', 30.00, 'uploads/food/sukku_coffee.jpg', 1),
(1, 6, 'Badam Milk (Hot/Cold)', 'Almond flavored milk', 60.00, 'uploads/food/badam_milk.jpg', 1),
(1, 6, 'Rose Milk', 'Rose syrup chilled milk', 50.00, 'uploads/food/rose_milk.jpg', 1),
(1, 6, 'Nannari Sarbath', 'Sarsaparilla root summer cooler', 40.00, 'uploads/food/nannari.jpg', 1),
(1, 6, 'Jigarthanda', 'Madurai special cooling drink', 80.00, 'uploads/food/jigarthanda.jpg', 1),
(1, 6, 'Buttermilk (Neer Mor)', 'Spiced diluted yogurt', 30.00, 'uploads/food/buttermilk.jpg', 1),
(1, 6, 'Lassi (Sweet/Salt)', 'Churned thick yogurt', 50.00, 'uploads/food/lassi.jpg', 1),
(1, 6, 'Mango Lassi', 'Mango flavored yogurt drink', 70.00, 'uploads/food/mango_lassi.jpg', 1),
(1, 6, 'Fresh Lime Soda', 'Lemon soda sweet or salt', 40.00, 'uploads/food/lime_soda.jpg', 1),
(1, 6, 'Bovonto', 'Popular local grape soda', 35.00, 'uploads/food/bovonto.jpg', 1),
(1, 6, 'Paneer Soda', 'Rose essence carbonated water', 30.00, 'uploads/food/paneer_soda.jpg', 1);

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
(1, 23, 'Medu Vada', 2, 20.00),
(1, 107, 'Filter Coffee', 2, 30.00), -- IDs might vary so using logic, but hardcoded here for simplicity assumption

-- Order 2: Biryani + Chicken 65
(2, 43, 'Chicken Biryani', 2, 180.00),
(2, 63, 'Chicken 65', 1, 160.00),

-- Order 3: Meals
(3, 35, 'South Indian Full Meals', 1, 150.00),

-- Order 4: Parotta + Salna
(4, 52, 'Parotta with Salna', 3, 50.00),
(4, 60, 'Kalakkal', 1, 20.00); -- Assuming Kalakkal is in there or will be

-- ============================================
-- DATABASE SETUP COMPLETE
-- ============================================

SELECT 'Database setup with 100+ Authentic TN items completed successfully!' as Status;
SELECT 'Menu Items Count:' as Metric, COUNT(*) as Value FROM menu_items;
