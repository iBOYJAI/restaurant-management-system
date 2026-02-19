<?php

/**
 * Category Management - Redesigned with 'Vibre' Aesthetic
 */
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

// Admins and Chefs can manage categories
requireAnyRole(['super_admin', 'admin', 'chef', 'manager']);
$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Vibre Premium</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
            background: #fafafa;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-xl);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: var(--space-lg);
        }

        .vibre-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            padding: var(--space-lg);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        .vibre-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }

        .vibre-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.05) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        .vibre-card:hover::before {
            opacity: 1;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .cat-icon {
            width: 48px;
            height: 48px;
            background: #f0f0f0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .cat-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }

        .cat-meta {
            font-size: 0.8rem;
            color: #888;
            margin-top: 4px;
            font-weight: 500;
        }

        .status-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-pill.active {
            background: #e6f7ef;
            color: #1fb977;
            border: 1px solid rgba(31, 185, 119, 0.1);
        }

        .status-pill.hidden {
            background: #fef0f0;
            color: #f56c6c;
            border: 1px solid rgba(245, 108, 108, 0.1);
        }

        .pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-pill.active .pill-dot {
            box-shadow: 0 0 10px #1fb977;
            animation: glow 2s infinite;
        }

        @keyframes glow {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .btn-vibre {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: white;
            color: #555;
        }

        .btn-vibre:hover {
            background: #f8f9fa;
            border-color: #ddd;
            color: #1a1a1a;
        }

        .btn-vibre-danger:hover {
            background: #fef0f0;
            color: #f56c6c;
            border-color: #fcc;
        }

        .order-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            color: #bbb;
            background: #f8f8f8;
            padding: 4px 8px;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="header-section">
                <div>
                    <h1 style="font-family: 'Outfit', sans-serif;">Categories</h1>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Structure your menu with style.</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()" style="border-radius: 14px; padding: 12px 24px;">
                    <span style="margin-right: 8px;">✨</span> New Category
                </button>
            </div>

            <div class="category-grid" id="categoryList">
                <!-- Skeletons -->
                <div class="vibre-card" style="opacity: 0.5;">Fething...</div>
            </div>
        </main>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content" style="max-width: 450px; border-radius: 24px; border: none;">
            <div class="modal-header" style="border: none; padding: 2rem 2.5rem 1rem;">
                <h3 id="modalTitle" style="font-family: 'Outfit', sans-serif;">Edit Category</h3>
                <button class="close-modal" onclick="closeModal('categoryModal')">×</button>
            </div>
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <div class="modal-body" style="padding: 1rem 2.5rem 2.5rem;">
                    <input type="hidden" id="categoryId">
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 700; color: #333; margin-bottom: 8px;">Name</label>
                        <input type="text" id="categoryName" class="form-control" required placeholder="E.g. Signature Sushi" style="border-radius: 12px; height: 50px;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 700; color: #333; margin-bottom: 8px;">Priority Order</label>
                        <input type="number" id="displayOrder" class="form-control" value="0" style="border-radius: 12px; height: 50px;">
                        <small style="color: #999;">Lower numbers appear first.</small>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; background: #f8f9fa; padding: 14px; border-radius: 14px; border: 1px solid #eee;">
                            <input type="checkbox" id="isActive" checked style="width: 20px; height: 20px; accent-color: var(--primary);">
                            <span style="font-weight: 600; color: #444;">Visible on Menu</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.5rem 2.5rem; background: #fafafa; border-radius: 0 0 24px 24px; border: none;">
                    <button type="button" class="btn-vibre" style="flex: none; width: 100px;" onclick="closeModal('categoryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1; border-radius: 12px; height: 44px;">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadCategories() {
            try {
                const response = await apiRequest('/backend/api/admin/categories.php');
                if (response.success) {
                    displayCategories(response.data);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayCategories(categories) {
            const container = document.getElementById('categoryList');
            if (categories.length === 0) {
                container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: white; border-radius: 24px; border: 1px dashed #ddd;"><p>No categories found.</p></div>';
                return;
            }

            container.innerHTML = categories.map(cat => `
                <div class="vibre-card">
                    <div class="order-badge">#${cat.display_order}</div>
                    <div class="card-top">
                        <div class="cat-info">
                            <div class="cat-title">${cat.name}</div>
                            <div class="cat-meta">Updated: ${formatDate(cat.updated_at || cat.created_at)}</div>
                        </div>
                    </div>
                    
                    <div>
                        <span class="status-pill ${cat.is_active == 1 ? 'active' : 'hidden'}">
                            <span class="pill-dot"></span>
                            ${cat.is_active == 1 ? 'Visible' : 'Hidden'}
                        </span>
                    </div>

                    <div class="card-actions">
                        <button class="btn-vibre" onclick='editCategory(${JSON.stringify(cat)})'>
                            <img src="../assets/icons/edit.svg" style="width:14px; opacity:0.6;"> Edit
                        </button>
                        <button class="btn-vibre btn-vibre-danger" onclick="deleteCategory(${cat.id})">
                            <img src="../assets/icons/trash.svg" style="width:14px; opacity:0.6;"> Delete
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'New Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            openModal('categoryModal');
        }

        function editCategory(cat) {
            document.getElementById('modalTitle').textContent = 'Modify Category';
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('categoryName').value = cat.name;
            document.getElementById('displayOrder').value = cat.display_order;
            document.getElementById('isActive').checked = cat.is_active == 1;
            openModal('categoryModal');
        }

        async function saveCategory(event) {
            event.preventDefault();
            const id = document.getElementById('categoryId').value;
            const data = {
                name: document.getElementById('categoryName').value,
                display_order: parseInt(document.getElementById('displayOrder').value),
                is_active: document.getElementById('isActive').checked ? 1 : 0
            };
            if (id) data.id = parseInt(id);

            const response = await apiRequest('/backend/api/admin/categories.php', id ? 'PUT' : 'POST', data);
            if (response.success) {
                showToast('Category saved', 'success');
                closeModal('categoryModal');
                loadCategories();
            }
        }

        async function deleteCategory(id) {
            if (!confirm('Permanent delete? This affects all sub-items.')) return;
            const response = await apiRequest('/backend/api/admin/categories.php', 'DELETE', {
                id
            });
            if (response.success) {
                showToast('Deleted', 'success');
                loadCategories();
            }
        }

        document.addEventListener('DOMContentLoaded', loadCategories);
    </script>
</body>

</html>