<?php
// Shared Header for Public Pages
if (!function_exists('isActive')) {
    function isActive($path)
    {
        $current = basename($_SERVER['PHP_SELF']);
        return ($current === $path || ($path === 'index.php' && ($current === '' || $current === '/'))) ? 'active' : '';
    }
}
$storeName = 'Obito Ani Foodzz';
try {
    if (file_exists(__DIR__ . '/../../backend/config/database.php')) {
        require_once __DIR__ . '/../../backend/config/database.php';
        $r = $pdo->query("SELECT name FROM restaurants WHERE id = 1");
        if ($r) {
            $n = $r->fetchColumn();
            if ($n) $storeName = $n;
        }
    }
} catch (Exception $e) {}
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-brand">
            <img src="assets/icons/orders.svg" alt="Logo">
            <span><?= htmlspecialchars($storeName) ?></span>
        </a>
        <ul class="navbar-nav">
            <li><a href="index.php" class="<?= isActive('index.php') ?>">Menu</a></li>
            <li><a href="reviews.php" class="<?= isActive('reviews.php') ?>">Reviews</a></li>
            <li>
                <a href="cart.php" class="<?= isActive('cart.php') ?>">
                    Cart
                    <span class="nav-cart-count hidden">0</span>
                </a>
            </li>

            <?php if (isLoggedIn()): ?>
                <!-- Staff Portal Links -->
                <?php if (hasAnyRole(['admin', 'super_admin', 'manager'])): ?>
                    <li><a href="admin/dashboard.php" style="color: var(--primary); font-weight: 700;">Admin</a></li>
                <?php endif; ?>

                <?php if (hasAnyRole(['admin', 'super_admin', 'chef', 'kitchen_staff'])): ?>
                    <li><a href="kitchen/dashboard.php" style="color: var(--primary); font-weight: 700;">Kitchen</a></li>
                <?php endif; ?>

                <li><a href="admin/logout.php" class="btn btn-outline btn-sm" style="padding: 0.4rem 1rem; margin-left: 10px;">Logout</a></li>
            <?php else: ?>
                <!-- Staff Access -->
                <li style="margin-left: 10px;">
                    <a href="admin/login.php" class="btn btn-primary btn-sm" style="color: white; padding: 0.5rem 1.25rem;">Staff Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>