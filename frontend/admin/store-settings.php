<?php

/**
 * Store Settings - Change store name (e.g. Obito Ani Foodzz)
 */
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

requireAnyRole(['super_admin', 'admin']);
$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Settings - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .settings-card { max-width: 500px; padding: 2rem; border: 1px solid var(--border); border-radius: var(--radius-xl); background: white; box-shadow: var(--shadow-md); }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-secondary); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="d-flex justify-between align-center mb-4">
                <div>
                    <h1>Store Settings</h1>
                    <p style="color: var(--text-secondary);">Update your restaurant display name.</p>
                </div>
            </div>
            <div class="settings-card">
                <form id="storeForm" onsubmit="saveStore(event)">
                    <div class="form-group">
                        <label class="form-label">Store Name</label>
                        <input type="text" id="storeName" class="form-control" required placeholder="e.g. Obito Ani Foodzz" value="">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>
        async function loadStore() {
            const resp = await apiRequest('/backend/api/admin/restaurant.php');
            if (resp.success && resp.data) {
                document.getElementById('storeName').value = resp.data.name || 'Obito Ani Foodzz';
            }
        }
        async function saveStore(e) {
            e.preventDefault();
            const name = document.getElementById('storeName').value.trim();
            if (!name) return;
            const basePath = window.location.pathname.split('/').slice(0, 2).join('/') || '';
            const resp = await fetch(basePath + '/backend/api/admin/restaurant.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ name: name })
            });
            const res = await resp.json();
            if (res.success) {
                showToast('Store name updated to "' + name + '"', 'success');
            } else {
                showToast(res.message || 'Failed to update', 'error');
            }
        }
        document.addEventListener('DOMContentLoaded', loadStore);
    </script>
</body>
</html>
