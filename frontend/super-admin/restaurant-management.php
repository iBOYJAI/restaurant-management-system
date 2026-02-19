<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAuth();
requireRole('super_admin');

require_once __DIR__ . '/../../backend/config/database.php';
$stmt = $pdo->query("SELECT * FROM restaurants ORDER BY created_at DESC");
$restaurants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--gradient-dark);
            color: white;
            padding: var(--space-md);
            position: fixed;
            height: 100vh;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: var(--space-xs);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: var(--space-lg);
            background: var(--bg-secondary);
        }

        .restaurant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--space-md);
        }

        .restaurant-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .restaurant-logo {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-full);
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: var(--space-md);
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">🏢 Super Admin</div>
            <ul class="sidebar-nav">
                <li><a href="restaurant-management.php" class="active">🏪 Restaurants</a></li>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="d-flex justify-between align-center mb-4">
                <h1>🏪 Restaurant Management</h1>
                <button class="btn btn-primary" onclick="openAddModal()">➕ Add Restaurant</button>
            </div>

            <div class="restaurant-grid">
                <?php foreach ($restaurants as $rest): ?>
                    <div class="restaurant-card">
                        <div class="restaurant-logo" style="background: <?= $rest['primary_color'] ?>;">
                            🍽️
                        </div>
                        <h3><?= htmlspecialchars($rest['name']) ?></h3>
                        <div style="color: var(--text-secondary); margin: var(--space-sm) 0;">
                            <div>📍 <?= htmlspecialchars($rest['address']) ?></div>
                            <div>📞 <?= htmlspecialchars($rest['phone']) ?></div>
                            <div>✉️ <?= htmlspecialchars($rest['email']) ?></div>
                            <div>🔗 <?= htmlspecialchars($rest['slug']) ?></div>
                        </div>
                        <div style="margin-top: var(--space-md);">
                            <span class="badge <?= $rest['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $rest['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div style="margin-top: var(--space-md); display: flex; gap: var(--space-xs);">
                            <button class="btn btn-sm btn-outline" onclick='editRestaurant(<?= json_encode($rest) ?>)'>Edit</button>
                            <button class="btn btn-sm btn-<?= $rest['is_active'] ? 'warning' : 'success' ?>" onclick="toggleStatus(<?= $rest['id'] ?>, <?= $rest['is_active'] ?>)">
                                <?= $rest['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <div id="restaurantModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 id="modalTitle">Add Restaurant</h3>
                <button class="close-modal" onclick="closeModal('restaurantModal')">×</button>
            </div>
            <form id="restaurantForm" onsubmit="saveRestaurant(event)">
                <div class="modal-body">
                    <input type="hidden" id="restaurantId">
                    <div class="form-group">
                        <label class="form-label">Restaurant Name *</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug (URL) *</label>
                        <input type="text" id="slug" class="form-control" required placeholder="restaurant-name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea id="address" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" id="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Primary Color</label>
                        <input type="color" id="primaryColor" class="form-control" value="#FF6B35">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('restaurantModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Restaurant';
            document.getElementById('restaurantForm').reset();
            document.getElementById('restaurantId').value = '';
            openModal('restaurantModal');
        }

        function editRestaurant(rest) {
            document.getElementById('modalTitle').textContent = 'Edit Restaurant';
            document.getElementById('restaurantId').value = rest.id;
            document.getElementById('name').value = rest.name;
            document.getElementById('slug').value = rest.slug;
            document.getElementById('address').value = rest.address || '';
            document.getElementById('phone').value = rest.phone || '';
            document.getElementById('email').value = rest.email || '';
            document.getElementById('primaryColor').value = rest.primary_color || '#FF6B35';
            openModal('restaurantModal');
        }

        async function saveRestaurant(event) {
            event.preventDefault();
            const id = document.getElementById('restaurantId').value;

            const data = {
                name: document.getElementById('name').value,
                slug: document.getElementById('slug').value,
                address: document.getElementById('address').value,
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                primary_color: document.getElementById('primaryColor').value
            };

            if (id) data.id = id;

            showToast('Restaurant saved! (Backend API placeholder)', 'success');
            closeModal('restaurantModal');
            setTimeout(() => window.location.reload(), 1000);
        }

        function toggleStatus(id, currentStatus) {
            const action = currentStatus ? 'deactivate' : 'activate';
            if (confirm(`Are you sure you want to ${action} this restaurant?`)) {
                showToast(`Restaurant ${action}d (Backend API placeholder)`, 'success');
                setTimeout(() => window.location.reload(), 1000);
            }
        }
    </script>
</body>

</html>