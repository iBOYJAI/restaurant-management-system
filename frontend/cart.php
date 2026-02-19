<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Restaurant</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .cart-page {
            padding: var(--space-xl) 0;
            min-height: 80vh;
        }

        .cart-item {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            display: flex;
            gap: var(--space-md);
            align-items: center;
            box-shadow: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--radius-md);
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: var(--space-xs);
            color: var(--primary);
        }

        .cart-item-notes {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        .cart-item-controls {
            display: flex;
            gap: var(--space-md);
            align-items: center;
        }

        .qty-input {
            width: 60px;
            text-align: center;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-weight: 600;
            background: var(--bg-secondary);
        }

        .remove-btn {
            color: var(--danger);
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            cursor: pointer;
            width: 36px;
            height: 36px;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }

        .remove-btn:hover {
            border-color: var(--danger);
            background: var(--bg-secondary);
        }

        .remove-btn img {
            width: 18px;
            height: 18px;
        }

        .cart-summary {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            position: sticky;
            top: calc(var(--space-lg) + 60px);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: var(--space-sm) 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            padding-top: var(--space-md);
        }

        .empty-cart {
            text-align: center;
            padding: var(--space-xl);
            color: var(--text-secondary);
        }

        /* Navbar tweaks to match index.php */
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
    <?php
    require_once __DIR__ . '/../backend/config/config.php';
    require_once __DIR__ . '/../backend/includes/auth.php';
    include_once __DIR__ . '/includes/navbar.php';
    ?>

    <div class="container cart-page">
        <h1 class="mb-4">Shopping Cart</h1>

        <div class="d-flex gap-3" style="align-items: flex-start;">
            <!-- Cart Items -->
            <div style="flex: 2;">
                <div id="cartItems"></div>

                <!-- Empty State -->
                <div id="emptyCart" class="empty-cart" style="display: none;">
                    <h2>Your cart is empty</h2>
                    <p>Add some delicious items to get started!</p>
                    <a href="index.php" class="btn btn-primary mt-3">Browse Menu</a>
                </div>
            </div>

            <!-- Cart Summary -->
            <div style="flex: 1;" id="summarySection">
                <div class="cart-summary">
                    <h3 class="mb-3">Order Summary</h3>

                    <div class="form-group">
                        <label class="form-label">Table Number *</label>
                        <input type="text" id="tableNumber" class="form-control" placeholder="Enter table number" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Special Instructions (Optional)</label>
                        <textarea id="specialNotes" class="form-control" placeholder="Any special requests..."></textarea>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">₹0.00</span>
                    </div>

                    <div class="summary-row">
                        <span>CGST (2.5%):</span>
                        <span id="cgstAmount">₹0.00</span>
                    </div>

                    <div class="summary-row">
                        <span>SGST (2.5%):</span>
                        <span id="sgstAmount">₹0.00</span>
                    </div>

                    <div class="summary-row">
                        <span>Items:</span>
                        <span id="itemCount">0</span>
                    </div>

                    <div class="summary-row">
                        <span>Total:</span>
                        <span id="total">₹0.00</span>
                    </div>

                    <button class="btn btn-primary" style="width: 100%; margin-top: var(--space-md);" onclick="placeOrder()">
                        Place Order
                    </button>

                    <a href="index.php" class="btn btn-outline" style="width: 100%; margin-top: var(--space-sm); display: inline-block; text-align: center;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function renderCart() {
            const cart = Cart.get();
            const cartItems = document.getElementById('cartItems');
            const emptyCart = document.getElementById('emptyCart');
            const summarySection = document.getElementById('summarySection');

            if (cart.length === 0) {
                cartItems.innerHTML = '';
                emptyCart.style.display = 'block';
                summarySection.style.display = 'none';
                return;
            }

            emptyCart.style.display = 'none';
            summarySection.style.display = 'block';

            cartItems.innerHTML = cart.map((item, index) => {
                let displayImg = item.image_url || 'assets/images/placeholder.jpg';
                if (displayImg.startsWith('uploads/')) {
                    displayImg = '../' + displayImg;
                }
                return `
                <div class="cart-item">
                    <img src="${displayImg}" alt="${item.name}" class="cart-item-image" onerror="this.src='assets/images/placeholder.jpg'">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${formatCurrency(item.price)} each</div>
                        ${item.notes ? `<div class="cart-item-notes">Note: ${item.notes}</div>` : ''}
                    </div>
                    <div class="cart-item-controls">
                        <input type="number" class="qty-input" value="${item.quantity}" min="1" 
                               onchange="updateQuantity(${index}, this.value)">
                        <button class="remove-btn" onclick="removeItem(${index})" title="Remove">
                            <img src="assets/icons/trash.svg" alt="Remove">
                        </button>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                            ${formatCurrency(item.price * item.quantity)}
                        </div>
                    </div>
                </div>
            `;
            }).join('');

            updateSummary(cart);
        }

        function updateSummary(cart) {
            const subtotal = Cart.getTotal();
            const itemCount = Cart.getCount();
            const taxRate = 0.025; // 2.5% each for CGST and SGST
            const cgst = subtotal * taxRate;
            const sgst = subtotal * taxRate;
            const total = subtotal + cgst + sgst;

            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('cgstAmount').textContent = formatCurrency(cgst);
            document.getElementById('sgstAmount').textContent = formatCurrency(sgst);
            document.getElementById('itemCount').textContent = itemCount;
            document.getElementById('total').textContent = formatCurrency(total);
        }

        function updateQuantity(index, quantity) {
            Cart.updateQuantity(index, quantity);
            renderCart();
            showToast('Cart updated', 'success');
        }

        function removeItem(index) {
            if (confirm('Remove this item from cart?')) {
                Cart.remove(index);
                renderCart();
                showToast('Item removed', 'info');
            }
        }

        async function placeOrder() {
            const tableNumber = document.getElementById('tableNumber').value.trim();

            if (!tableNumber) {
                showToast('Please enter table number', 'warning');
                document.getElementById('tableNumber').focus();
                return;
            }

            const cart = Cart.get();

            if (cart.length === 0) {
                showToast('Cart is empty', 'warning');
                return;
            }

            const orderData = {
                table_number: tableNumber,
                special_notes: document.getElementById('specialNotes').value.trim(),
                items: cart.map(item => ({
                    id: item.id,
                    quantity: item.quantity,
                    notes: item.notes || ''
                }))
            };

            try {
                const response = await apiRequest('/backend/api/orders.php', 'POST', orderData);

                if (response.success) {
                    Cart.clear();
                    localStorage.setItem('last_order', JSON.stringify(response.data));
                    window.location.href = 'order-confirmation.php';
                } else {
                    showToast(response.message || 'Error placing order', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error placing order', 'error');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
        });
    </script>
</body>

</html>