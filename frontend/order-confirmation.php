<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt - Restaurant</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-secondary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-md);
        }

        .receipt-container {
            width: 100%;
            max-width: 400px;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
        }

        .receipt {
            background: white;
            padding: var(--space-lg);
            position: relative;
            clip-path: polygon(0% 0%, 100% 0%, 100% 100%,
                    98% 99%, 96% 100%, 94% 99%, 92% 100%, 90% 99%, 88% 100%, 86% 99%, 84% 100%, 82% 99%, 80% 100%, 78% 99%, 76% 100%, 74% 99%, 72% 100%, 70% 99%, 68% 100%, 66% 99%, 64% 100%, 62% 99%, 60% 100%, 58% 99%, 56% 100%, 54% 99%, 52% 100%, 50% 99%, 48% 100%, 46% 99%, 44% 100%, 42% 99%, 40% 100%, 38% 99%, 36% 100%, 34% 99%, 32% 100%, 30% 99%, 28% 100%, 26% 99%, 24% 100%, 22% 99%, 20% 100%, 18% 99%, 16% 100%, 14% 99%, 12% 100%, 10% 99%, 8% 100%, 6% 99%, 4% 100%, 2% 99%,
                    0% 100%);
            padding-bottom: 40px;
        }

        .receipt::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient-primary);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: var(--space-lg);
            border-bottom: 2px dashed var(--border);
            padding-bottom: var(--space-md);
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto var(--space-sm);
            display: block;
        }

        .receipt-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: var(--space-xs);
        }

        .receipt-meta {
            font-family: 'Space Mono', monospace;
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: var(--space-xs);
        }

        .success-badge {
            display: inline-block;
            background: var(--success);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: var(--space-sm);
        }

        .receipt-items {
            margin-bottom: var(--space-lg);
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-sm);
            font-size: 0.95rem;
        }

        .item-qty {
            font-weight: 700;
            margin-right: 8px;
            min-width: 20px;
        }

        .item-name {
            flex: 1;
        }

        .item-price {
            font-weight: 600;
        }

        .receipt-total {
            border-top: 2px dashed var(--border);
            padding-top: var(--space-md);
            margin-top: var(--space-md);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .receipt-footer {
            text-align: center;
            margin-top: var(--space-xl);
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .actions {
            margin-top: var(--space-lg);
            display: grid;
            gap: var(--space-sm);
        }

        .dotted-line {
            border-top: 2px dashed var(--border);
            margin: var(--space-md) 0;
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <div class="receipt">
            <div class="receipt-header">
                <img src="assets/icons/orders.svg" alt="Logo" class="logo-icon">
                <div class="receipt-title">TAX INVOICE</div>
                <div class="receipt-meta">Gobichettipalayam, Erode, Tamil Nadu, India</div>
                <div class="receipt-meta">GSTIN: 33AAAAA0000A1Z5 | +91-9876543210</div>
                <div class="success-badge">Payment Pending (Cash)</div>
            </div>

            <div id="receiptContent">
                <!-- Loaded via JS -->
                <div class="text-center p-4">Loading receipt...</div>
            </div>

            <div class="receipt-footer">
                <p>Thank you for dining with us!</p>
                <div class="actions">
                    <button class="btn btn-outline w-full" onclick="window.print()">🖨️ Print Receipt</button>
                    <button class="btn btn-outline w-full" onclick="window.location.href='index.php'">Place Another Order</button>
                    <button class="btn btn-primary w-full" id="feedbackBtn">Rate Your Experience</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const orderData = localStorage.getItem('last_order');

            if (!orderData) {
                window.location.href = 'index.php';
                return;
            }

            const order = JSON.parse(orderData);

            // Set feedback button action
            document.getElementById('feedbackBtn').onclick = () => {
                window.location.href = `feedback.php?order_id=${order.id}`;
            };

            const itemsHtml = order.items.map(item => `
                <div class="receipt-item">
                    <span class="item-qty">${item.quantity}</span>
                    <span class="item-name">${item.menu_item_name}</span>
                    <span class="item-price">${formatCurrency(item.price * item.quantity)}</span>
                </div>
            `).join('');

            document.getElementById('receiptContent').innerHTML = `
                <div class="text-center mb-4">
                    <div class="receipt-meta">ORDER #${order.order_number}</div>
                    <div class="receipt-meta">${formatDate(order.created_at)}</div>
                    <div class="receipt-meta" style="font-weight: 700; color: var(--text-primary); margin-top: 8px;">TABLE ${order.table_number}</div>
                </div>

                <div class="receipt-items">
                    ${itemsHtml}
                </div>

                <div class="receipt-total">
                    <div class="d-flex justify-between mb-2">
                        <span>Subtotal</span>
                        <span>${formatCurrency(order.total_amount - (order.tax_amount || 0))}</span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span>CGST (2.5%)</span>
                        <span>${formatCurrency((order.tax_amount || 0) / 2)}</span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span>SGST (2.5%)</span>
                        <span>${formatCurrency((order.tax_amount || 0) / 2)}</span>
                    </div>
                    <div class="total-row mt-2">
                        <span>GRAND TOTAL</span>
                        <span>${formatCurrency(order.total_amount)}</span>
                    </div>
                </div>

                ${order.special_notes ? `
                    <div class="dotted-line"></div>
                    <div style="font-size: 0.85rem;">
                        <strong>Note:</strong> ${order.special_notes}
                    </div>
                ` : ''}
            `;
        });
    </script>
</body>

</html>