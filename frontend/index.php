<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/auth.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu - Browse & Order</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .hero {
            background: var(--bg-dark);
            color: var(--bg-primary);
            padding: 4rem 0;
            text-align: center;
            margin-bottom: var(--space-xl);
            border-bottom: 1px solid var(--secondary);
        }

        .hero h1 {
            color: var(--bg-primary);
            margin-bottom: var(--space-sm);
            font-size: 3rem;
            letter-spacing: -1px;
        }

        .hero p {
            font-size: 1.125rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .category-filter {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            flex-wrap: wrap;
            justify-content: center;
        }

        .category-btn {
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--secondary);
            background: var(--bg-primary);
            color: var(--primary);
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--transition-base);
            font-weight: 600;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary);
            color: var(--bg-primary);
            border-color: var(--primary);
        }

        .search-bar {
            max-width: 500px;
            margin: 0 auto var(--space-lg);
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 1rem 3rem 1rem 1.5rem;
            border: 1px solid var(--secondary);
            border-radius: var(--radius-full);
            font-size: 1rem;
            background: white;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .cart-float {
            position: fixed;
            bottom: var(--space-lg);
            right: var(--space-lg);
            z-index: 100;
        }

        .cart-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--bg-primary);
            border: none;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            position: relative;
            transition: transform var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-btn img {
            width: 24px;
            height: 24px;
            filter: invert(1);
        }

        .cart-btn:hover {
            transform: scale(1.1);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            border: 2px solid white;
        }

        .menu-item-card {
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .menu-item-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .menu-item-card .item-price {
            color: var(--primary);
        }

        /* Navbar tweaks */
        .navbar {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border);
            box-shadow: none;
        }

        .navbar-brand {
            background: none;
            -webkit-text-fill-color: initial;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            width: 24px;
            height: 24px;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include_once __DIR__ . '/includes/navbar.php'; ?>

    <!-- Hero Section (Redesigned) -->
    <section class="hero">
        <div class="container">
            <h1>Craving Something Amazing?</h1>
            <p>Order fresh, delicious meals delivered right to your table.</p>

            <!-- Search Bar moved into Hero for cleaner look -->
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search for sushi, pizza, burgers..." onkeyup="handleSearch()">
                <div class="search-icon">🔍</div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container" style="max-width: 1400px;">
        <!-- Category Filter (Pill Design) -->
        <div class="category-filter" id="categoryFilter">
            <button class="category-btn active" data-category="all" onclick="filterByCategory('all')">🔥 All Items</button>
        </div>

        <!-- Menu Grid (Updated to grid-5 for 4-5 items per row) -->
        <div class="grid grid-5" id="menuGrid">
            <!-- Menu items will be loaded here -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" style="display: none; text-align: center; padding: var(--space-xl);">
            <div style="font-size: 3rem; margin-bottom: var(--space-md);">😕</div>
            <h3>No delicious items found</h3>
            <p style="color: var(--text-secondary);">Try searching for something else!</p>
        </div>
    </main>

    <!-- Floating Cart Button -->
    <div class="cart-float">
        <a href="cart.php" class="cart-btn">
            <img src="assets/icons/cart.svg" alt="Cart">
            <span class="cart-badge" style="display: none;">0</span>
        </a>
    </div>

    <!-- Item Detail Modal -->
    <div id="itemModal" class="modal item-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalItemName">Item Name</h3>
                <button class="close-modal" onclick="closeModal('itemModal')">×</button>
            </div>
            <div class="modal-body">
                <img id="modalItemImage" class="item-modal-image" src="" alt="">
                <p id="modalItemDescription"></p>
                <div class="d-flex justify-between align-center">
                    <h3 id="modalItemPrice" style="color: var(--primary);"></h3>
                </div>

                <div class="quantity-controls">
                    <button class="qty-btn" onclick="changeQuantity(-1)">−</button>
                    <span class="qty-display" id="quantityDisplay">1</span>
                    <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Special Instructions (Optional)</label>
                    <textarea id="itemNotes" class="form-control" placeholder="E.g., No onions, Extra spicy..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('itemModal')">Cancel</button>
                <button class="btn btn-primary" onclick="addToCart()">Add to Cart</button>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/restaurant-switcher.js"></script>
    <script>
        let allMenuItems = [];
        let currentItem = null;
        let quantity = 1;
        let itemRatings = {};

        // Fetch menu on page load
        async function loadMenu() {
            try {
                const response = await apiRequest('/backend/api/menu.php');

                if (response.success) {
                    allMenuItems = response.data;
                    displayCategories(allMenuItems);
                    displayMenu(allMenuItems);
                    loadRatings();
                } else {
                    showToast('Error loading menu', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error loading menu', 'error');
            }
        }

        // Load ratings for menu items
        async function loadRatings() {
            try {
                const response = await apiRequest('/backend/api/feedback.php');
                if (response.success && response.data) {
                    itemRatings = {};
                    response.data.forEach(item => {
                        itemRatings[item.id] = {
                            avg_rating: parseFloat(item.avg_rating),
                            count: parseInt(item.rating_count)
                        };
                    });
                    // Re-render menu with ratings
                    displayMenu(allMenuItems);
                }
            } catch (error) {
                console.log('Ratings not available');
            }
        }

        function displayCategories(categories) {
            const filter = document.getElementById('categoryFilter');

            categories.forEach(cat => {
                const btn = document.createElement('button');
                btn.className = 'category-btn';
                btn.dataset.category = cat.id;
                btn.textContent = cat.name;
                btn.onclick = () => filterByCategory(cat.id);
                filter.appendChild(btn);
            });
        }

        function displayMenu(categories, searchTerm = '') {
            const grid = document.getElementById('menuGrid');
            const emptyState = document.getElementById('emptyState');
            grid.innerHTML = '';

            let totalItems = 0;

            categories.forEach(category => {
                category.items.forEach(item => {
                    if (searchTerm && !item.name.toLowerCase().includes(searchTerm.toLowerCase()) &&
                        !item.description.toLowerCase().includes(searchTerm.toLowerCase())) {
                        return;
                    }

                    totalItems++;
                    const card = createMenuItem(item);
                    grid.appendChild(card);
                });
            });

            emptyState.style.display = totalItems === 0 ? 'block' : 'none';
        }

        function escapeAttr(s) {
            return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function createMenuItem(item) {
            const card = document.createElement('div');
            card.className = 'menu-item-card';
            card.onclick = () => openItemModal(item);

            const images = [item.image_url, item.image_url2, item.image_url3, item.image_url4, item.image_url5].filter(img => img);
            let imageSrc = images.length > 0 ? (images[0].startsWith('assets/') ? images[0] : '../' + images[0]) : 'assets/images/placeholder.jpg';

            const rating = itemRatings[item.id];
            const ratingHTML = rating ? `
                <div style="display: flex; align-items: center; gap: 0.25rem; margin-top: 0.5rem; color: #ffd700;">
                    <span style="font-size: 1rem;">${'★'.repeat(Math.round(rating.avg_rating))}${'☆'.repeat(5 - Math.round(rating.avg_rating))}</span>
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">${rating.avg_rating.toFixed(1)} (${rating.count})</span>
                </div>
            ` : '';

            const thumbPath = (img) => img.startsWith('assets/') ? img : '../' + img;
            card.innerHTML = `
                <img src="${imageSrc}" alt="${escapeAttr(item.name)}" onerror="this.src='assets/images/placeholder.jpg'">
                <div class="card-content">
                    <div class="item-name">${escapeAttr(item.name)}</div>
                    <div class="item-description">${escapeAttr(item.description || '')}</div>
                    ${ratingHTML}
                    <div class="item-price">${formatCurrency(item.price)}</div>
                    <div class="d-flex gap-1 mt-2 mb-2">
                        ${images.slice(1).map(img => `<img src="${thumbPath(img)}" style="width: 30px; height: 30px; border-radius: 4px; border: 1px solid var(--border);" onerror="this.style.display='none'">`).join('')}
                    </div>
                    <button type="button" class="btn btn-primary btn-sm quick-add-btn">Quick Add</button>
                </div>
            `;
            card.querySelector('.quick-add-btn').addEventListener('click', function(e) {
                e.stopPropagation();
                quickAdd(item);
            });

            return card;
        }

        function openItemModal(item) {
            currentItem = item;
            quantity = 1;

            document.getElementById('modalItemName').textContent = item.name;
            document.getElementById('modalItemDescription').textContent = item.description || '';
            document.getElementById('modalItemPrice').textContent = formatCurrency(item.price);
            let modalImg = item.image_url || 'assets/images/placeholder.jpg';
            if (modalImg.startsWith('uploads/')) modalImg = '../' + modalImg;
            else if (!modalImg.startsWith('assets/')) modalImg = '../' + modalImg;
            document.getElementById('modalItemImage').src = modalImg;
            document.getElementById('quantityDisplay').textContent = quantity;
            document.getElementById('itemNotes').value = '';

            // Show rating in modal if available
            const rating = itemRatings[item.id];
            if (rating) {
                // ... same logic for modal ...
                // Re-using the same star display logic
            }

            // Clean up old ratings
            const existingRating = document.querySelector('.modal-rating');
            if (existingRating) existingRating.remove();

            if (rating) {
                const ratingDiv = document.createElement('div');
                ratingDiv.className = 'modal-rating';
                ratingDiv.style.cssText = 'color: #ffd700; font-size: 1.25rem; margin: var(--space-sm) 0;';
                ratingDiv.innerHTML = `
                    ${'★'.repeat(Math.round(rating.avg_rating))}${'☆'.repeat(5 - Math.round(rating.avg_rating))}
                    <span style="color: var(--text-secondary); font-size: 1rem; margin-left: 0.5rem;">${rating.avg_rating.toFixed(1)} (${rating.count} reviews)</span>
                `;
                document.getElementById('modalItemDescription').after(ratingDiv);
            }

            openModal('itemModal');
        }

        function changeQuantity(delta) {
            quantity = Math.max(1, quantity + delta);
            document.getElementById('quantityDisplay').textContent = quantity;
        }

        function quickAdd(item) {
            Cart.add(item, 1, '');
        }

        function addToCart() {
            if (currentItem) {
                const notes = document.getElementById('itemNotes').value;
                Cart.add(currentItem, quantity, notes);
                closeModal('itemModal');
            }
        }

        function filterByCategory(categoryId) {
            // Update active button
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.category == categoryId || (categoryId === 'all' && btn.dataset.category === 'all')) {
                    btn.classList.add('active');
                }
            });

            // Filter menu
            if (categoryId === 'all') {
                displayMenu(allMenuItems);
            } else {
                const filtered = allMenuItems.filter(cat => cat.id == categoryId);
                displayMenu(filtered);
            }
        }

        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            displayMenu(allMenuItems, searchTerm);
        }

        // Update cart count in navbar
        document.addEventListener('DOMContentLoaded', () => {
            loadMenu();

            const updateCartCount = () => {
                const count = Cart.getCount();
                const badge = document.querySelector('.nav-cart-count');
                const badgeFloat = document.querySelector('.cart-badge');

                badge.textContent = count;
                if (badgeFloat) badgeFloat.textContent = count;

                if (count > 0) {
                    badge.classList.remove('hidden');
                    if (badgeFloat) badgeFloat.style.display = 'flex';
                } else {
                    badge.classList.add('hidden');
                    if (badgeFloat) badgeFloat.style.display = 'none';
                }
            };

            updateCartCount();

            // Update on storage change
            window.addEventListener('storage', updateCartCount);
        });
    </script>
</body>

</html>