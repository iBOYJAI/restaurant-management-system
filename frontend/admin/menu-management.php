<?php

/**
 * Menu Management - Premium Layout with Multi-Image Support
 */
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

// Admins and Chefs manage the menu
requireAnyRole(['super_admin', 'admin', 'chef']);
$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Premium View</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .menu-table-card {
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table th {
            text-align: left;
            background: var(--bg-secondary);
            padding: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border);
        }

        .item-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .item-image-stack {
            display: flex;
            position: relative;
            width: 60px;
            height: 60px;
        }

        .item-image-stack img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: var(--radius-md);
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
        }

        .item-image-stack img:nth-child(2) {
            position: absolute;
            left: 10px;
            top: 10px;
            z-index: 1;
            opacity: 0.7;
        }

        .image-upload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .image-slot {
            aspect-ratio: 1;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .image-slot:hover {
            border-color: var(--primary);
            background: rgba(255, 107, 53, 0.05);
        }

        .image-slot.has-image {
            border-style: solid;
            border-color: #eee;
        }

        .image-slot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slot-label {
            font-size: 0.65rem;
            color: #adb5bd;
            margin-top: 4px;
            font-weight: 700;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--success);
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Menu Management</h1>
                    <p style="color: var(--text-secondary);">Create and manage your restaurant's offerings.</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">➕ Create New Dish</button>
            </div>

            <div class="menu-table-card">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Dish</th>
                            <th>Details</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody id="menuItemsList">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 4rem;">Loading Dishes...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Dish Modal -->
    <div id="itemModal" class="modal">
        <div class="modal-content" style="max-width: 800px; padding: 0;">
            <div class="modal-header" style="padding: 1.5rem 2rem;">
                <h3 id="modalTitle">Dish Details</h3>
                <button class="close-modal" onclick="closeModal('itemModal')">×</button>
            </div>
            <form id="itemForm" onsubmit="saveItem(event)">
                <div class="modal-body" style="padding: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <input type="hidden" id="itemId">

                    <div class="col-info">
                        <div class="form-group mb-3">
                            <label class="form-label">Dish Name *</label>
                            <input type="text" id="itemName" class="form-control" required placeholder="E.g. Spicy Ramen">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Category *</label>
                            <select id="itemCategory" class="form-control" required></select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Price ($) *</label>
                            <input type="number" step="0.01" id="itemPrice" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="itemDescription" class="form-control" rows="4" placeholder="Briefly describe the ingredients and taste..."></textarea>
                        </div>
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" id="itemAvailable" checked style="width: 20px; height: 20px; accent-color: var(--success);">
                                <strong>Active for Ordering</strong>
                            </label>
                        </div>
                    </div>

                    <div class="col-media">
                        <label class="form-label">Visual Gallery (Max 5)</label>
                        <p style="font-size: 0.7rem; color: var(--text-light); margin-bottom: 10px;">The first image will be the primary cover.</p>

                        <div class="image-upload-grid">
                            <!-- Slot 1 (Primary) -->
                            <div class="image-slot" onclick="triggerInput(1)" id="slot1">
                                <span style="font-size: 1.5rem;">📸</span>
                                <span class="slot-label">COVER</span>
                                <input type="file" id="imageInput1" hidden accept="image/*" onchange="previewImage(1)">
                            </div>
                            <!-- Slots 2-5 -->
                            <div class="image-slot" onclick="triggerInput(2)" id="slot2"><span class="slot-label">IMG 2</span><input type="file" id="imageInput2" hidden accept="image/*" onchange="previewImage(2)"></div>
                            <div class="image-slot" onclick="triggerInput(3)" id="slot3"><span class="slot-label">IMG 3</span><input type="file" id="imageInput3" hidden accept="image/*" onchange="previewImage(3)"></div>
                            <div class="image-slot" onclick="triggerInput(4)" id="slot4"><span class="slot-label">IMG 4</span><input type="file" id="imageInput4" hidden accept="image/*" onchange="previewImage(4)"></div>
                            <div class="image-slot" onclick="triggerInput(5)" id="slot5"><span class="slot-label">IMG 5</span><input type="file" id="imageInput5" hidden accept="image/*" onchange="previewImage(5)"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1.5rem 2rem; background: #f8f9fa; border-top: 1px solid #eee;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('itemModal')">Discard</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        let categories = [];

        async function init() {
            await loadCategories();
            await loadMenuItems();
        }

        async function loadCategories() {
            const resp = await apiRequest('/backend/api/admin/categories.php');
            if (resp.success) {
                categories = resp.data;
                const select = document.getElementById('itemCategory');
                select.innerHTML = categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            }
        }

        async function loadMenuItems() {
            const resp = await apiRequest('/backend/api/admin/menu-items.php');
            if (resp.success) displayItems(resp.data);
        }

        function displayItems(items) {
            const list = document.getElementById('menuItemsList');
            if (items.length === 0) {
                list.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 4rem;">No items found.</td></tr>';
                return;
            }

            list.innerHTML = items.map(item => `
                <tr style="height: 80px;">
                    <td>
                        <div class="item-image-stack">
                            <img src="../../${item.image_url || 'assets/images/placeholder.jpg'}" onerror="this.src='../assets/images/placeholder.jpg'">
                            ${item.image_url2 ? `<img src="../../${item.image_url2}">` : ''}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--bg-dark);">${item.name}</div>
                        <div style="font-size: 0.75rem; color: var(--text-light); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${item.description || 'No description'}
                        </div>
                    </td>
                    <td><span class="badge" style="background: #eee; color: #666;">${item.category_name}</span></td>
                    <td style="font-weight: 700; color: var(--primary);">${formatCurrency(item.price)}</td>
                    <td>
                        <label class="toggle-switch">
                            <input type="checkbox" ${item.is_available == 1 ? 'checked' : ''} onchange="toggleAvailability(${item.id})">
                            <span class="slider"></span>
                        </label>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline" onclick='editItem(${JSON.stringify(item)})'>Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function triggerInput(i) {
            document.getElementById(`imageInput${i}`).click();
        }

        function previewImage(i) {
            const input = document.getElementById(`imageInput${i}`);
            const slot = document.getElementById(`slot${i}`);
            if (input && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    slot.innerHTML = `<img src="${e.target.result}"><input type="file" id="imageInput${i}" hidden accept="image/*" onchange="previewImage(${i})">`;
                    slot.classList.add('has-image');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Create New Dish';
            document.getElementById('itemForm').reset();
            document.getElementById('itemId').value = '';
            for (let i = 1; i <= 5; i++) {
                const slot = document.getElementById(`slot${i}`);
                slot.innerHTML = (i === 1 ? '<span style="font-size: 1.5rem;">📸</span><span class="slot-label">COVER</span>' : `<span class="slot-label">IMG ${i}</span>`) +
                    `<input type="file" id="imageInput${i}" hidden accept="image/*" onchange="previewImage(${i})">`;
                slot.classList.remove('has-image');
            }
            openModal('itemModal');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Modify Dish';
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemDescription').value = item.description || '';
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemCategory').value = item.category_id;
            document.getElementById('itemAvailable').checked = item.is_available == 1;

            const images = [item.image_url, item.image_url2, item.image_url3, item.image_url4, item.image_url5];
            images.forEach((url, i) => {
                const slot = document.getElementById(`slot${i+1}`);
                const label = (i === 0) ? '<span style="font-size: 1.5rem;">📸</span><span class="slot-label">COVER</span>' : `<span class="slot-label">IMG ${i+1}</span>`;
                if (url) {
                    slot.innerHTML = `<img src="../../${url}">` + label + `<input type="file" id="imageInput${i+1}" hidden accept="image/*" onchange="previewImage(${i+1})">`;
                    slot.classList.add('has-image');
                } else {
                    slot.innerHTML = label + `<input type="file" id="imageInput${i+1}" hidden accept="image/*" onchange="previewImage(${i+1})">`;
                    slot.classList.remove('has-image');
                }
            });
            openModal('itemModal');
        }

        async function saveItem(event) {
            event.preventDefault();
            const id = document.getElementById('itemId').value;
            const formData = new FormData();
            formData.append('name', document.getElementById('itemName').value);
            formData.append('description', document.getElementById('itemDescription').value);
            formData.append('price', document.getElementById('itemPrice').value);
            formData.append('category_id', document.getElementById('itemCategory').value);
            formData.append('is_available', document.getElementById('itemAvailable').checked ? '1' : '0');

            if (id) formData.append('id', id);

            for (let i = 1; i <= 5; i++) {
                const input = document.getElementById(`imageInput${i}`);
                if (!input || !input.files || !input.files[0]) continue;
                const file = input.files[0];
                formData.append(i === 1 ? 'image' : `image${i}`, file);
            }

            try {
                const resp = await fetch('../../backend/api/admin/menu-items.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await resp.json();
                if (res.success) {
                    showToast('Dish saved successfully', 'success');
                    closeModal('itemModal');
                    loadMenuItems();
                } else {
                    showToast(res.message || 'Error saving dish', 'error');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function toggleAvailability(id) {
            const resp = await apiRequest('/backend/api/admin/menu-items.php', 'PATCH', {
                id
            });
            if (resp.success) showToast('Status updated', 'success');
        }

        async function deleteItem(id) {
            if (!confirm('Permanently remove this dish?')) return;
            const resp = await apiRequest('/backend/api/admin/menu-items.php', 'DELETE', {
                id
            });
            if (resp.success) {
                showToast('Dish deleted', 'success');
                loadMenuItems();
            }
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>

</html>