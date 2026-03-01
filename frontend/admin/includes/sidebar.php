<?php
$storeName = 'Obito Ani Foodzz';
try {
    if (file_exists(__DIR__ . '/../../../backend/config/database.php')) {
        require_once __DIR__ . '/../../../backend/config/database.php';
        $r = $pdo->query("SELECT name FROM restaurants WHERE id = 1");
        if ($r) {
            $n = $r->fetchColumn();
            if ($n) $storeName = $n;
        }
    }
} catch (Exception $e) {}
// Function to check if a link is active
function isActive($path)
{
    $current = $_SERVER['PHP_SELF'];
    // For kitchen, check if we are in the kitchen folder
    if (strpos($path, '../kitchen/') !== false) {
        return strpos($current, '/kitchen/') !== false ? 'active' : '';
    }
    // For admin pages, check if we match exactly
    return basename($current) === $path ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/icons/lock.svg" alt="Admin" style="width: 24px; filter: invert(1);">
        <?= htmlspecialchars($storeName ?? 'Admin Panel') ?>
    </div>
    <ul class="sidebar-nav">
        <!-- Dashboard: Primarily for Management -->
        <?php if (hasAnyRole(['admin', 'super_admin', 'manager'])): ?>
            <li><a href="dashboard.php" class="<?= isActive('dashboard.php') ?>"><img src="../assets/icons/home.svg" alt=""> Dashboard</a></li>
        <?php endif; ?>

        <!-- Kitchen View: For all Staff, but prioritized for Kitchen roles -->
        <?php if (hasAnyRole(['admin', 'super_admin', 'chef', 'kitchen_staff', 'manager'])): ?>
            <li><a href="../kitchen/dashboard.php" class="<?= isActive('../kitchen/dashboard.php') ?>"><img src="../assets/icons/clipboard.svg" alt=""> Kitchen View</a></li>
        <?php endif; ?>

        <!-- Menu Management: For Admin and Chefs -->
        <?php if (hasAnyRole(['admin', 'super_admin', 'chef'])): ?>
            <li><a href="menu-management.php" class="<?= isActive('menu-management.php') ?>"><img src="../assets/icons/menu.svg" alt=""> Menu Management</a></li>
            <li><a href="category-management.php" class="<?= isActive('category-management.php') ?>"><img src="../assets/icons/folder.svg" alt=""> Categories</a></li>
        <?php endif; ?>

        <!-- Shared / General -->
        <?php if (hasAnyRole(['admin', 'super_admin', 'manager', 'chef'])): ?>
            <li><a href="order-history.php" class="<?= isActive('order-history.php') ?>"><img src="../assets/icons/clipboard.svg" alt=""> Order History</a></li>
        <?php endif; ?>

        <?php if (hasAnyRole(['admin', 'super_admin', 'manager'])): ?>
            <li><a href="feedback-dashboard.php" class="<?= isActive('feedback-dashboard.php') ?>"><img src="../assets/icons/user.svg" alt=""> Feedback</a></li>
        <?php endif; ?>

        <?php if (hasAnyRole(['admin', 'super_admin'])): ?>
            <li><a href="store-settings.php" class="<?= isActive('store-settings.php') ?>"><img src="../assets/icons/menu.svg" alt=""> Store Settings</a></li>
        <?php endif; ?>
        <li style="margin-top: auto; padding-top: var(--space-md); border-top: 1px solid var(--secondary);">
            <a href="logout.php" class="logout-link"><img src="../assets/icons/logout.svg" alt=""> Logout</a>
        </li>
    </ul>

    <?php if (isset($admin)): ?>
        <div style="position: absolute; bottom: var(--space-md); left: var(--space-md); right: var(--space-md);">
            <div style="padding: var(--space-sm); border: 1px solid var(--secondary); border-radius: var(--radius-md); background: rgba(255,255,255,0.02);">
                <div style="font-size: 0.75rem; color: var(--text-light); opacity: 0.7;">Logged in as</div>
                <div style="font-weight: 600; color: var(--bg-primary);"><?= htmlspecialchars($admin['full_name'] ?? $admin['name'] ?? 'Admin') ?></div>
                <div style="font-size: 0.7rem; color: var(--primary); font-weight: 700; text-transform: uppercase; margin-top: 2px;"><?= htmlspecialchars($admin['role_display'] ?? $admin['role'] ?? 'Staff') ?></div>
            </div>
        </div>
    <?php endif; ?>
</aside>
<style>
    /* Inline critical sidebar styles to ensure consistency if external CSS fails or lags */
    .sidebar {
        width: 250px;
        background: var(--bg-dark);
        color: var(--bg-primary);
        padding: var(--space-md);
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        border-right: 1px solid var(--secondary);
        left: 0;
        top: 0;
        z-index: 100;
    }

    .sidebar-brand {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: var(--space-lg);
        padding-bottom: var(--space-md);
        border-bottom: 1px solid var(--secondary);
        display: flex;
        align-items: center;
        gap: var(--space-sm);
    }

    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin-bottom: var(--space-xs);
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: var(--space-sm);
        padding: 0.75rem 1rem;
        color: var(--text-light);
        border-radius: var(--radius-md);
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
    }

    .sidebar-nav a img {
        width: 20px;
        height: 20px;
        filter: invert(0.6);
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
        background: var(--bg-primary);
        color: var(--bg-dark);
        border-color: var(--bg-primary);
    }

    .sidebar-nav a:hover img,
    .sidebar-nav a.active img {
        filter: invert(0);
    }

    .sidebar-nav a.logout-link:hover {
        background: rgba(234, 84, 85, 0.1);
        color: #ea5455;
        border-color: rgba(234, 84, 85, 0.2);
    }

    .sidebar-nav a.logout-link:hover img {
        filter: invert(32%) sepia(85%) saturate(1637%) hue-rotate(334deg) brightness(97%) contrast(89%);
    }

    .main-content {
        flex: 1;
        margin-left: 250px;
        padding: var(--space-lg);
        background: var(--bg-primary);
        min-height: 100vh;
    }
</style>