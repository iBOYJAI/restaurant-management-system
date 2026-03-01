# RESTAURANT MANAGEMENT SYSTEM

# PROJECT REPORT

**Submitted to**

**DEPARTMENT OF AI&DS**

**GOBI ARTS & SCIENCE COLLEGE (AUTONOMOUS)**

**GOBICHETTIPALAYAM-638453**

---

**By**

**HARI**

**(23-AI-136)**

---

**Guided By**

**R. RENUKADEVI, M.Sc. (CS)., M.Phil.,**

---

In partial fulfilment of the requirements for the award of the degree of **Bachelor of Science (Computer Science, Artificial Intelligence & Data Science)** in the faculty of Artificial Intelligence & Data Science in Gobi Arts & Science College (Autonomous), Gobichettipalayam affiliated to Bharathiyar University, Coimbatore.

**MAY 2026**

---

## CERTIFICATES

**CERTIFICATES**

This is to certify that the project report entitled "RESTAURANT MANAGEMENT SYSTEM" is a bonafide work done by HARI (23AI136) under my supervision and guidance.

                                 Signature of Guide	:
                                     Name 			: R. RENUKADEVI
                                     Designation 		: Assistant Professor
                                     Department 		: Computer Science (AI & DS)
                                     Date 		          :


Counter Signed


Head of the Department 						Principal


Viva-Voce held on: ___________


Internal Examiner					External Examiner

---

## DECLARATION

**DECLARATION**

I hereby declare that the project report entitled "RESTAURANT MANAGEMENT SYSTEM" submitted to the Principal, Gobi Arts & Science College (Autonomous), Gobichettypalayam, in partial fulfilment of the requirements for the award of degree of Bachelor of Science (Computer Science, Artificial Intelligence & Data Science) is a record of project work done by me during the period of study in this college under the supervision and guidance of R.RENUKADEVI, M.Sc.(CS).,M.Phil., Head of the Department of Artificial Intelligence & Data Science.

Signature		:
Name			: HARI
Register Number	: 23-AI-136
Date			:

---

## ACKNOWLEDGEMENT

**ACKNOWLEDGEMENT**

The success and final outcome of this project, "Restaurant Management System," required a lot of guidance and assistance from many people, and I am extremely privileged to have received this throughout the completion of my project. All that I have done is only due to such supervision and assistance, and I would not forget to thank them.
I respect and thank Dr. M. Ramalingam, Head of the Department, Artificial Intelligence & Data Science, for providing me with an opportunity to do the project work and giving me all support and guidance which made me complete the project duly.
I owe my deep gratitude to my internal guide R. RENUKADEVI, M.Sc. (CS)., M.Phil., who took a keen interest in my project work and guided me throughout, till the completion of my project work, by providing all necessary information for developing a good system.
I would not forget to remember the Principal, Gobi Arts & Science College, Gobichettypalayam, for his encouragement and timely support and guidance till the completion of my project work.
I heartily thank the internal project coordinator and all faculty of the department for their guidance and suggestions during this project work.
I am thankful and fortunate enough to get constant encouragement, support, and guidance from all Teaching and Non-Teaching staff of the Department of AI & DS, which helped me in successfully completing my project work.
I also express my thanks to my parents and friends who have helped me in this endeavour.

HARI

---

## SYNOPSIS

**SYNOPSIS**

The Restaurant Management System is developed to address the operational challenges faced by modern dine-in restaurants in managing orders, kitchen coordination, and customer feedback efficiently. In conventional restaurant environments, order processing is often handled manually through paper-based methods. This approach frequently leads to errors such as illegible handwriting, misplaced order slips, incorrect billing, and delays in communication between waiters and kitchen staff. Furthermore, printed menus lack flexibility, as they cannot dynamically reflect daily availability changes or price updates. Customer feedback is typically collected informally and not stored in a centralized system, making it difficult for management to analyze service quality and improve operations. Additionally, many existing restaurant automation solutions are expensive enterprise-level systems or depend heavily on constant internet connectivity, which may not be feasible for small and medium-sized restaurants operating within limited budgets.

To overcome these limitations, this project proposes a web-based client–server Restaurant Management System that automates the complete dine-in ordering workflow while maintaining high data accuracy and security. The system is designed to operate efficiently within a Local Area Network (LAN) environment using a XAMPP stack, making it cost-effective and accessible. Internet connectivity is required only during initial setup for optional features such as automated food image integration. The system ensures structured data management using a normalized MySQL database with InnoDB storage engine and implements role-based access control to restrict functionality according to user roles such as Administrator, Kitchen Staff, and Customer.

The primary objective of the system is to provide customers with a seamless and intuitive digital menu interface. Customers can browse menu categories, view item descriptions and prices, add selected items to a persistent cart using LocalStorage, and place orders linked to a specific table number. The system automatically calculates applicable taxes, including CGST and SGST, ensuring billing accuracy and transparency. Once an order is placed, it is instantly transmitted to the kitchen interface, eliminating manual communication gaps and reducing service delays.

For restaurant administrators, the system offers comprehensive management functionality. Administrators can perform Create, Read, Update, and Delete (CRUD) operations on menu categories and items, modify pricing, toggle item availability in real time, and manage daily offerings efficiently. The system provides access to complete order history records, enabling tracking of sales and operational performance. Administrators can also update order statuses and respond to customer feedback directly through the system, ensuring a centralized communication and record-keeping mechanism.

A significant component of the system is the Kitchen Display System (KDS), which enhances coordination between front-of-house staff and kitchen personnel. The KDS displays active orders in real time, categorized by their preparation status. Kitchen staff can update the order status from "Placed" to "Preparing" and then to "Ready" with a single click. This streamlined workflow reduces confusion, prevents duplicate preparation, and improves overall service speed and table turnover efficiency. The elimination of paper-based order slips enhances cleanliness and organization within the kitchen environment.

The system follows a three-tier architecture consisting of the presentation layer, business logic layer, and data layer. The presentation layer is developed using HTML5, CSS3, and vanilla JavaScript (ES6) to provide a responsive and user-friendly interface. The business logic layer is implemented using PHP 8, which handles API requests, validation, authentication, and processing of orders. The data layer utilizes MySQL 8 with a fully normalized schema to ensure data consistency, reduce redundancy, and maintain referential integrity through foreign key constraints. This structured architecture ensures scalability, maintainability, and future extensibility of the system.

The implementation covers the entire order lifecycle, including menu browsing, cart management, order placement, real-time kitchen processing, status tracking, billing, feedback collection, and administrative reporting. The database design supports multi-restaurant expansion, enabling future scalability into a multi-tenant architecture where multiple restaurant branches can operate under a single centralized system.

In conclusion, the Restaurant Management System provides a reliable, efficient, and secure solution for restaurants seeking digital transformation of their dine-in operations. By automating order processing, enabling real-time communication, and centralizing data management, the system significantly reduces manual errors, improves operational transparency, enhances customer satisfaction, and supports scalable business growth. This project demonstrates how a cost-effective web-based solution can modernize restaurant workflows while maintaining simplicity, performance, and data integrity.

---

## CONTENTS

**CONTENTS**

| S.No | Chapter | Title | Page No. |
|------|---------|-------|----------|
| | ACKNOWLEDGEMENT | | i |
| | SYNOPSIS | | ii |
| 1 | INTRODUCTION | | 1 |
| 1.1 | | About the Project | |
| 1.2 | | Hardware Specification | |
| 1.3 | | Software Specification | |
| 2 | SYSTEM ANALYSIS | | |
| 2.1 | | Problem Definition | |
| 2.2 | | System Study | |
| 2.3 | | Proposed System | |
| 3 | SYSTEM DESIGN | | |
| 3.1 | | Data Flow Diagram | |
| 3.2 | | E–R Diagram | |
| 3.3 | | File Specification | |
| 3.4 | | Module Specification | |
| 4 | TESTING AND IMPLEMENTATION | | |
| 5 | CONCLUSION AND SUGGESTIONS | | |
| | BIBLIOGRAPHY | | |
| | APPENDICES | | |
| A | Appendix – A (Screen Formats) | | 23 |

---

## CHAPTER 1 — INTRODUCTION

**INTRODUCTION**

Web-based applications have transformed how businesses manage daily operations. In the food and hospitality sector, the shift from paper menus and handwritten orders to digital systems has become essential for improving accuracy, speed, and customer satisfaction. Modern restaurant management systems integrate customer-facing ordering interfaces, administrative dashboards, and kitchen display systems into a single cohesive platform, enabling real-time visibility and streamlined workflows.

The evolution of restaurant technology has moved from simple point-of-sale (POS) terminals to full-stack web applications that support table ordering, menu management, order tracking, and feedback collection. Key principles applied in such systems include: (1) User-centred design for both customers and staff; (2) Clear separation of roles (customer, admin, chef, waiter); (3) Data consistency through normalized databases; and (4) Responsive interfaces that work across devices. The Restaurant Management System leverages these principles to create a unified platform with a clean, professional aesthetic suitable for restaurant operations.

### 1.1 About the Project

The Restaurant Management System is a full-stack web application designed to address the operational challenges faced by restaurants in managing dine-in orders, kitchen workflow, and customer feedback.

**The Core Problem**

In a typical restaurant, waiters take orders on paper or memory, relay them verbally to the kitchen, and track status manually. This leads to order errors, delays, and poor visibility for managers. Printed menus cannot reflect real-time availability or price changes. Customer feedback is often lost or recorded in scattered formats. Scaling to multiple roles (admin, manager, chef, waiter) with different permissions is difficult with ad-hoc tools.

**The Solution**

The system provides an integrated, database-driven solution:

1. **Customer Interface**: Customers browse the digital menu (with category filters and search), add items to a cart with quantities and special instructions, and place orders by entering their table number. The cart is persisted in LocalStorage so it survives page refreshes. Tax (CGST/SGST) is calculated automatically.

2. **Admin Panel**: Administrators log in securely and manage categories and menu items (add, edit, delete, toggle availability). They view order history, update order status (placed → preparing → ready → served), print receipts, and manage customer feedback (view and reply). A dashboard shows real-time counters for pending orders, revenue, and active menu items.

3. **Kitchen Display System (KDS)**: Kitchen staff log in to a dedicated dashboard that auto-refreshes to show active orders. Orders are displayed as color-coded cards. Chefs can mark orders as "Cooking" or "Ready" with a single click, enabling waiters to know when to serve.

4. **Automation**: A setup script initializes the database and can fetch high-quality food images from the Pexels API for each menu item, storing them in the uploads folder and linking paths in the database.

**Project & Institution Profile**

This project is an academic project undertaken at Gobi Arts & Science College, Gobichettipalayam. It was developed by HARI (Roll No: 23AI136) under the guidance of R. RENUKADEVI, M.Sc.(CS)., M.Phil., Head of the Department of Artificial Intelligence & Data Science. Gobi Arts & Science College (Autonomous) is affiliated to Bharathiyar University, Coimbatore. The Department of Artificial Intelligence & Data Science prepares students for technology-driven careers and applies full-stack web technologies, database design, and system analysis in projects such as this Restaurant Management System.

**Primary Objectives**

1. **Automate Order Capture**: Provide a digital interface for customers to browse the menu and place orders by table number with automatic tax calculation and order number generation.
2. **Unify Stakeholder Views**: Enable administrators to manage menu and orders, and kitchen staff to view and update order status in real time from a single system.
3. **Ensure Data Integrity**: Design a normalized MySQL database with referential integrity between restaurants, users, categories, menu items, orders, order items, feedback, and notifications.
4. **Streamline Kitchen Workflow**: Implement a Kitchen Display System that auto-refreshes and allows status updates (placed → preparing → ready) with minimal clicks.
5. **Support Feedback Loop**: Allow customers to submit ratings and comments and allow admins to view and reply to feedback.

**Real-World Use Case**

This system is tailored for: dine-in restaurants and cafés; food courts; canteens; and any establishment that needs table-based ordering, kitchen coordination, and feedback management without relying on expensive enterprise software or constant internet connectivity.

### 1.2 Hardware Specification

To deploy the Restaurant Management System effectively, the following hardware infrastructure is required. The system is lightweight and does not require expensive server-grade hardware.

**Server (Admin/Kitchen/Backend PC)**

| Component | Specification | Justification |
|-----------|---------------|----------------|
| Processor | Intel Core i3 (5th Gen) / AMD Ryzen 3 or higher | Handles PHP, Apache, and MySQL for concurrent users |
| RAM | 4 GB DDR4 or higher | Smooth operation of XAMPP stack and browser sessions |
| Storage | 128 GB SSD or HDD | Database and uploaded images (food photos) |
| Network Interface | Ethernet (100 Mbps or higher) | LAN access for multiple devices if used |
| Operating System | Windows 10/11 or Linux | For running XAMPP or LAMP stack |

**Client (Customer / Staff Devices)**

| Component | Specification | Justification |
|-----------|---------------|----------------|
| Processor | Any modern CPU | Thin client (browser-based) |
| RAM | 2 GB or higher | Modern browser (Chrome, Edge, Firefox) |
| Browser | Google Chrome, Mozilla Firefox, Microsoft Edge (latest) | Compatibility with ES6 and CSS Grid/Flex |
| Display | 1366×768 or higher | Comfortable view of menu grid and cart |

**Network Infrastructure (Optional for multi-device)**

| Component | Specification |
|-----------|---------------|
| Switch / Router | 10/100/1000 Mbps for LAN |
| IP Configuration | Static IP for server if accessed from other devices (e.g. tablets for KDS) |

### 1.3 Software Specification

The robust functionality of the Restaurant Management System is built upon a stack of open-source, industry-standard software technologies.

**Backend Technology**

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.x | Server-side scripting language. Powers the core logic, session management, and API endpoints. |
| MySQL | 8.0 | Relational database management system (RDBMS). Stores restaurant data, menu items, orders, and feedback. Supports ACID properties via InnoDB engine. |

**Frontend Technology**

| Technology | Version | Purpose |
|------------|---------|---------|
| HTML5 | — | Provides the semantic structure of the webpages. |
| CSS3 | — | Custom layout (Grid/Flex), theme, responsive design. |
| JavaScript | Vanilla ES6+ | Cart logic, LocalStorage, AJAX/fetch for order placement and API calls. No framework dependency. |

**Development & Deployment Tools**

| Tool | Purpose |
|------|---------|
| XAMPP | Apache + MySQL + PHP for local development and deployment. |
| Pexels API | Free stock photos for menu item images (used by backend script during setup). |
| Git | Version control (optional). |
| Editor | VS Code or any PHP/HTML/JS editor. |

---

## CHAPTER 2 — SYSTEM ANALYSIS

### 2.1 Problem Definition

The traditional method of running restaurant operations—especially order taking and kitchen coordination—is plagued with inefficiencies and scope for error:

1. **Order Accuracy**: Handwritten or verbal orders are prone to illegibility, mishearing, and wrong table assignment. There is no single source of truth for "what was ordered" and "for which table."
2. **Kitchen Visibility**: Chefs often work from paper chits or memory. There is no real-time view of all pending orders, leading to delays and missed items.
3. **Menu Updates**: Printed menus cannot reflect daily specials, out-of-stock items, or price changes without reprinting.
4. **Feedback Fragmentation**: Customer reviews are collected on paper, third-party apps, or not at all, with no direct link to orders or ability for management to respond centrally.
5. **Lack of Analytics**: Without a digital system, managers have no easy way to see revenue, popular items, or order trends.

### 2.2 System Study

**Existing System**

The existing approach in many small and medium restaurants is manual or semi-manual:

- Waiters take orders on paper or memorized; orders are shouted or carried to the kitchen.
- Menu is printed; updates require reprinting.
- No unified dashboard for order status; staff walk to the kitchen to check.
- Feedback is ad-hoc (verbal or on external platforms).
- No integrated reporting or multi-role access control.

Drawbacks: High risk of order errors and wrong table delivery; no real-time visibility for managers or kitchen; no persistent cart or order history; does not scale to multiple roles with clear permissions.

**Detailed System Study**

A thorough system study was conducted through observation of existing restaurant workflows and stakeholder requirement gathering. Three primary user roles define the system architecture:

1. **Customer**: Browses menu, adds items to cart, places order with table number, views confirmation, and can submit feedback. No login required for ordering.
2. **Administrator**: Manages categories, menu items, availability; views and updates order status; views and replies to feedback; accesses dashboard, reports, and analytics. Full CRUD on menu and orders.
3. **Kitchen Staff (Chef)**: Logs in to KDS; sees list of active orders; updates status to Preparing or Ready. Minimal interface focused on speed and clarity.

**Feasibility Study**

- **Technical Feasibility**: The stack (PHP, MySQL, HTML/CSS/JS) is mature and widely documented. XAMPP provides a ready environment. Technical risk is low.
- **Economic Feasibility**: All core technologies are open-source. Deployment can be on a single PC or low-cost server. The system reduces order errors and improves turnaround.
- **Operational Feasibility**: Staff use familiar browser-based interfaces. Training effort is low. The system can run on existing LAN without mandatory internet after initial setup.
- **Behavioral Feasibility**: Customers benefit from a clear menu and easy ordering; kitchen staff get a single screen to manage orders; administrators gain control and visibility.

### 2.3 Proposed System

The Restaurant Management System introduces an integrated web-based platform.

**Benefits of the Proposed System**

- **Digital Order Capture**: Customers place orders via the web interface; each order is stored with table number, items, and timestamps.
- **Live Kitchen Display**: KDS shows active orders and allows status updates (Placed → Preparing → Ready) with one click.
- **Centralized Menu Management**: Admin can add, edit, delete categories and items and toggle availability without reprinting.
- **Unified Feedback**: Customers submit ratings and comments; admin can view and reply; data is linked to orders.
- **Role-Based Access**: Separate login and dashboards for admin and kitchen (and optionally manager, waiter) with appropriate permissions.
- **Automated Assets**: Pexels API script populates menu images automatically during setup.

**Comparison: Existing vs Proposed System**

| Feature | Existing System | Proposed System |
|---------|-----------------|-----------------|
| Order Capture | Paper / Verbal | Digital, table-based, stored in DB |
| Menu Updates | Reprint required | Real-time CRUD in admin panel |
| Kitchen View | Paper chits | Live KDS with status buttons |
| Order Status | Manual check | Placed → Preparing → Ready → Served |
| Feedback | Scattered / None | Centralized, with admin reply |
| Cart Persistence | None | LocalStorage (survives refresh) |
| Tax Calculation | Manual | Automatic (CGST/SGST) |
| Multi-Role | None | Admin, Chef, Waiter, Manager (RBAC) |
| Images | Manual upload | Optional Pexels auto-download |

---

## CHAPTER 3 — SYSTEM DESIGN

### 3.1 Data Flow Diagram

**DFD Level 0 — Context Diagram**

- **Customer** → System: Browse menu, place order, submit feedback.
- **Admin** → System: Manage menu, view/update orders, manage feedback.
- **Kitchen** → System: View orders, update status.
- **System** → Customer: Menu, confirmation, order status.
- **System** → Admin: Dashboards, reports.
- **System** → Kitchen: Order list, status updates.

**DFD Level 1 — Major Processes**

- Process 1: **Menu & Cart** — Customer views menu (from DB), adds to cart (LocalStorage), submits order (POST to backend).
- Process 2: **Order Processing** — Backend validates order, inserts into `orders` and `order_items`, calculates total/tax, returns order number.
- Process 3: **Kitchen Update** — KDS polls or loads orders; chef clicks status; backend updates `orders.status`.
- Process 4: **Menu Management** — Admin CRUD on `categories` and `menu_items`; changes reflect in customer menu.
- Process 5: **Feedback** — Customer submits feedback → stored in `feedback` and optionally `item_ratings`; admin reads and replies (update `feedback`).

**DFD Level 2 — Order Fulfilment Sub-process**

Validate table number and cart payload → Fetch menu item IDs and prices → Insert `orders` row → Insert `order_items` rows → Optionally create notification → Return order number and confirmation data.

### 3.2 E–R Diagram

**Entity Relationship (Summary)**

- **restaurants** (id, name, slug, address, phone, email, primary_color, logo_url, is_active, created_at, updated_at) — root for multi-tenant.
- **roles** (id, name, display_name, level) — super_admin, admin, manager, waiter, chef.
- **users** (id, restaurant_id, role_id, username, password_hash, full_name, phone, is_active, last_login, created_at, updated_at) — staff accounts.
- **categories** (id, restaurant_id, name, description, display_order, is_active, created_at, updated_at) — menu sections.
- **menu_items** (id, restaurant_id, category_id, name, description, price, image_url, image_url2–5, is_available, created_at, updated_at) — dishes.
- **orders** (id, restaurant_id, order_number, table_number, total_amount, tax_amount, status, special_notes, created_at, updated_at) — order header.
- **order_items** (id, order_id, menu_item_id, menu_item_name, quantity, price, item_notes, created_at) — line items.
- **feedback** (id, restaurant_id, order_id, customer_name, customer_email, overall_rating, food_quality, service_rating, ambience_rating, comments, admin_response, responded_by, responded_at, created_at) — reviews.
- **item_ratings** (id, feedback_id, menu_item_id, rating, comment, created_at) — per-item ratings.
- **notifications** (id, restaurant_id, user_id, title, message, type, priority, related_order_id, is_read, read_at, created_at) — alerts.

Relationships: restaurants has many users, categories, menu_items, orders, feedback, notifications; categories have many menu_items; orders have many order_items and at most one feedback; feedback can have many item_ratings.

### 3.3 File Specification

**Table 1: restaurants**

| Column | Data Type | Constraint | Description |
|--------|-----------|------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Unique identifier |
| name | VARCHAR(255) | NOT NULL | Restaurant name |
| slug | VARCHAR(100) | UNIQUE, NOT NULL | URL-friendly identifier |
| address | TEXT | NULL | Address |
| phone | VARCHAR(20) | NULL | Contact phone |
| email | VARCHAR(255) | NULL | Contact email |
| primary_color | VARCHAR(7) | DEFAULT '#FF6B35' | Theme color |
| logo_url | VARCHAR(255) | NULL | Logo path |
| is_active | TINYINT(1) | DEFAULT 1 | Active flag |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Table 2: roles** — id, name, display_name, level.

**Table 3: users** — id, restaurant_id (FK), role_id (FK), username, password_hash, full_name, phone, is_active, last_login, created_at, updated_at.

**Table 4: categories** — id, restaurant_id (FK), name, description, display_order, is_active, created_at, updated_at.

**Table 5: menu_items** — id, restaurant_id (FK), category_id (FK), name, description, price, image_url, image_url2–5, is_available, created_at, updated_at.

**Table 6: orders** — id, restaurant_id (FK), order_number, table_number, total_amount, tax_amount, status (ENUM: placed, preparing, ready, served, cancelled), special_notes, created_at, updated_at.

**Table 7: order_items** — id, order_id (FK), menu_item_id (FK), menu_item_name, quantity, price, item_notes, created_at.

**Table 8: feedback** — id, restaurant_id (FK), order_id (FK), customer_name, customer_email, overall_rating, food_quality, service_rating, ambience_rating, comments, admin_response, responded_by, responded_at, created_at.

**Table 9: item_ratings** — id, feedback_id (FK), menu_item_id (FK), rating, comment, created_at.

**Table 10: notifications** — id, restaurant_id (FK), user_id (FK), title, message, type, priority, related_order_id (FK), is_read, read_at, created_at.

### 3.4 Module Specification

**Customer Module**: (1) Browse menu (index.php) — categories, search, item modal; (2) Cart (cart.php) — view, edit quantity/notes, tax display; (3) Place order — table number, submit; (4) Confirmation (order-confirmation.php) — order number, total; (5) Feedback (reviews.php / feedback form) — submit rating and comments.

**Admin Module**: (1) Login (admin/login.php); (2) Dashboard (admin/dashboard.php) — stats, status board; (3) Category management (admin/category-management.php) — CRUD; (4) Menu management (admin/menu-management.php) — CRUD, availability; (5) Order history (admin/order-history.php) — list, status update, print; (6) Feedback dashboard (admin/feedback-dashboard.php) — view, reply; (7) Reports, Analytics — optional.

**Kitchen Module**: (1) Login (kitchen/login.php); (2) Dashboard (kitchen/dashboard.php) — order list, status buttons (Preparing, Ready), auto-refresh.

**Technical Modules**: Authentication (auth.php) — login, session, requireAuth(), logout; Database (config/database.php) — PDO, UTF8MB4; Order API (backend/api/orders.php); Menu API (backend/api/menu.php); Setup (backend/setup_db.php).

---

## CHAPTER 4 — TESTING AND IMPLEMENTATION

### 4.1 System Testing

System testing ensures the entire system functions correctly and meets the specified requirements. It verifies that all integrated components work together as expected and helps identify any defects before deployment. The system is tested by executing different test cases and evaluating the results. The main objective is to validate the system's behaviour under real-world conditions.

### 4.2 Unit Testing

| Test ID | Test Case | Expected Result | Actual Result | Status |
|---------|-----------|-----------------|---------------|--------|
| UT-01 | Admin login with valid credentials | Redirect to dashboard | Redirected correctly | PASS |
| UT-02 | Admin login with invalid password | Error message | Error shown | PASS |
| UT-03 | Add item to cart on frontend | Cart count increases, LocalStorage updated | Cart updated | PASS |
| UT-04 | Place order with valid table number | Order created, confirmation shown | Order in DB, confirmation page | PASS |
| UT-05 | Place order with empty cart | Validation error | Submission blocked | PASS |
| UT-06 | Kitchen update status to Preparing | orders.status = 'preparing' | Status updated | PASS |
| UT-07 | Password hashing (bcrypt) | Hash ≠ plaintext | Bcrypt used | PASS |
| UT-08 | Category CRUD in admin | Records in categories table | CRUD working | PASS |

### 4.3 Integration Testing

| Scenario | Description | Status |
|----------|-------------|--------|
| IT-01 | Customer adds items → places order → Admin sees order → Kitchen updates status → Customer flow complete | PASS |
| IT-02 | Admin adds category and menu item → Item appears on customer menu | PASS |
| IT-03 | Customer submits feedback → Admin sees in feedback dashboard → Admin replies → Reply visible on frontend | PASS |
| IT-04 | Multiple orders placed in sequence → All stored with unique order_number | PASS |

### 4.4 Validation Testing

| Test Case | Input | Expected Output | Status |
|-----------|-------|-----------------|--------|
| Empty table number on order | Table = "" | Validation error | PASS |
| Invalid email in feedback | "notanemail" | Error or sanitization | PASS |
| Direct access to admin/dashboard.php without login | No session | Redirect to login | PASS |
| Kitchen user cannot access admin panel | Kitchen role | Access denied or redirect | PASS |

### 4.5 Implementation Environment

| Component | Specification |
|-----------|---------------|
| OS | Windows 10/11 or Linux |
| Stack | XAMPP (Apache, MySQL, PHP 8) |
| Database | MySQL 8.0, InnoDB, utf8mb4_unicode_ci |
| Access | http://localhost/restaurant/ (or project path) |

### 4.6 Security Measures

- Passwords stored with bcrypt (password_hash).
- Session-based auth for admin and kitchen; session regeneration on login.
- PDO prepared statements for all DB queries (SQL injection prevention).
- Output escaped (htmlspecialchars) where applicable to reduce XSS risk.
- Role checks on admin and kitchen pages (requireAuth / role_id).

### 4.7 User Acceptance Testing (UAT)

UAT involves real users (staff and customers) testing the system in a production-like environment. Scenarios tested: Complete order workflow; Cart persistence after refresh; Feedback and admin reply. Final UAT Status: **APPROVED** — System ready for deployment.

---

## CHAPTER 5 — CONCLUSION AND SUGGESTIONS

### 5.1 Conclusion

The Restaurant Management System has been successfully designed, developed, and tested to meet the objectives of a modern dine-in ordering and kitchen management platform. The system addresses the core problems of manual order capture, lack of kitchen visibility, and fragmented feedback by providing a unified web application with distinct interfaces for customers, administrators, and kitchen staff.

By integrating a normalized MySQL database with a PHP backend and vanilla JavaScript frontend, the project delivers a maintainable, scalable solution that can run on a standard XAMPP stack. The use of LocalStorage for cart persistence improves customer experience; the Kitchen Display System reduces communication gaps; and the feedback module enables continuous improvement. The project demonstrates the practical application of database design, web technologies, and role-based access control within the B.Sc. (CS, AI & DS) curriculum.

### 5.2 Suggestions for Future Work

1. **Online Payments**: Integrate payment gateways (e.g., Razorpay, Stripe) for prepaid orders or online settlements.
2. **Mobile App / PWA**: Develop a mobile-friendly or Progressive Web App for customers and optionally for kitchen tablets.
3. **Predictive Analytics**: Use order and feedback data for demand forecasting, popular item analysis, and recommendation engines.
4. **Multi-Branch Dashboard**: Extend the existing multi-tenant schema to support centralized reporting across multiple restaurant branches.
5. **Inventory Integration**: Link menu item availability to stock levels and reorder alerts.
6. **Real-Time KDS**: Replace polling with WebSocket or Server-Sent Events for instant order and status updates in the kitchen.

### 5.3 Achievements

- Full ordering lifecycle implemented (menu → cart → order → confirmation).
- Admin CRUD for categories and menu items with availability toggle.
- KDS with status workflow (Placed → Preparing → Ready) and clear UI.
- Feedback and rating storage with admin reply capability.
- Database design with referential integrity and support for multi-restaurant expansion.
- Optional automation for menu images via Pexels API.

### 5.4 Limitations

- No integrated payment processing; orders are recorded for later billing.
- No automatic inventory deduction; availability is manually toggled.
- KDS refresh is interval-based (e.g., 30 s) unless enhanced with WebSocket.
- Pexels API key is stored in backend config; production should use environment variables.

---

## BIBLIOGRAPHY

1. Nixon, R. (2021). Learning PHP, MySQL & JavaScript: With jQuery, CSS & HTML5. O'Reilly Media.
2. Silberschatz, A., Korth, H. F., & Sudarshan, S. (2019). Database System Concepts. McGraw-Hill Education.
3. Pressman, R. S. (2014). Software Engineering: A Practitioner's Approach. McGraw-Hill Education.
4. PHP Documentation. https://www.php.net/docs.php
5. MySQL 8.0 Reference Manual. https://dev.mysql.com/doc/refman/8.0/en/
6. Pexels API Documentation. https://www.pexels.com/api/
7. MDN Web Docs. https://developer.mozilla.org/

---

## APPENDIX – A (Screen Formats)

**Appendix – A (Screen Formats)** — Page 23

### Screenshots (Auto-Captured UI)

The following figures are taken from the running system using an automated Puppeteer script (`capture-screenshots.js`). Viewport images represent the visible browser window; full-page images capture the entire scrollable page.

- **Figure A.1** — Customer Landing / Menu (full page)  
  ![Customer Landing / Menu — full page](screenshots/full/home.png)

- **Figure A.2** — Cart and Order Summary (full page)  
  ![Cart and Order Summary — full page](screenshots/full/cart.png)

- **Figure A.3** — Order Confirmation (full page)  
  ![Order Confirmation — full page](screenshots/full/order-confirmation.png)

- **Figure A.4** — Admin Dashboard (viewport)  
  ![Admin Dashboard — viewport](screenshots/viewport/admin-dashboard.png)

- **Figure A.5** — Admin Menu Management (viewport)  
  ![Admin Menu Management — viewport](screenshots/viewport/admin-menu-management.png)

- **Figure A.6** — Kitchen Dashboard (full page)  
  ![Kitchen Dashboard — full page](screenshots/full/kitchen-dashboard.png)

- **Figure A.7** — Waiter Dashboard (full page)  
  ![Waiter Dashboard — full page](screenshots/full/waiter-dashboard.png)

### A.1 Common / Customer

1. **Landing / Menu (index.php)** — Main customer view. Elements: category pills, search, menu grid with images and prices, item modal (details, add to cart), cart icon with count.
2. **Cart (cart.php)** — Review and place order. Elements: list of items with quantity, notes, price; subtotal; tax (CGST/SGST); total; table number input; Place Order button. Cart loaded from LocalStorage.
3. **Order Confirmation (order-confirmation.php)** — Post-order summary. Elements: order number, table number, total, message to wait for service. Link to return to menu.
4. **Reviews / Feedback (reviews.php)** — Display testimonials and/or feedback form. Elements: list of feedback with ratings and admin replies; form for new feedback (name, email, rating, comments).

### A.2 Admin (frontend/admin/)

5. **Login (login.php)** — Secure entry. Elements: username, password, submit. Redirect to dashboard on success.
6. **Dashboard (dashboard.php)** — Overview. Elements: cards (pending orders, revenue, active items); status board or recent orders; navigation to menu, orders, feedback.
7. **Category Management (category-management.php)** — CRUD categories. Elements: list, Add form, Edit/Delete per row.
8. **Menu Management (menu-management.php)** — CRUD menu items. Elements: list by category, Add/Edit form (name, description, price, category, image, is_available), Delete.
9. **Order History (order-history.php)** — View and update orders. Elements: table (order number, table, total, status, date); status dropdown or buttons; print receipt option.
10. **Feedback Dashboard (feedback-dashboard.php)** — List of feedback (customer, rating, comments, order); reply text box and submit; display admin response.

### A.3 Kitchen (frontend/kitchen/)

11. **Login (login.php)** — Kitchen staff entry. Redirect to KDS dashboard.
12. **Kitchen Dashboard (dashboard.php)** — KDS. Elements: list/grid of active orders (order number, table, items, time); color-coded cards; buttons "Start Cooking" / "Ready"; auto-refresh (e.g., every 30 s).

### A.4 Detailed Screen Element Tables

- **Landing Page (index.php)**: System logo/name (header); Category pills; Search box; Menu grid (cards: image, name, price, Quick Add); Item modal; Cart icon with badge; Footer.
- **Cart Page (cart.php)**: Cart table (Item name, Quantity +/-, Unit price, Line total, Notes, Remove); Subtotal; Tax (CGST/SGST); Grand total; Table number input; Place Order button.
- **Order Confirmation**: Success message; Order number; Table number; Total; Message to wait for service; Link to menu.
- **Admin Dashboard**: Sidebar (Menu, Categories, Orders, Feedback, Reports, Logout); Cards (Pending Orders, Revenue, Active Items); Status board; Quick links.
- **Menu Management**: List (ID, Name, Category, Price, Availability toggle, Image, Edit, Delete); Add New; Form (Name, Description, Category, Price, Image, Is Available).
- **Order History**: Filters (date, status); Orders table (Order number, Table, Total, Status dropdown, Date, View, Print).
- **Kitchen Dashboard**: Header; Order cards (Order number, Table, Items, Time, Buttons Start Cooking / Ready); Color coding; Auto-refresh every 30 s.
- **Feedback Dashboard**: List (Customer, Order ID, Rating, Comments, Admin response); Reply text area and Submit.

---

**End of Report**
