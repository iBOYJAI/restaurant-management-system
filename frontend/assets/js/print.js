/**
 * Print Utilities - Offline Printer Integration
 * No external printer services - uses browser print API
 */

function printKitchenReceipt(order) {
    const printWindow = window.open('', '_blank');

    const itemsList = order.items.map(item => `
        <div class="kitchen-item">
            <span class="kitchen-qty">${item.quantity}x</span>
            <span class="kitchen-name">${item.menu_item_name}</span>
            ${item.item_notes ? `<div class="kitchen-notes">⚠️ ${item.item_notes}</div>` : ''}
        </div>
    `).join('');

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Kitchen Order #${order.order_number}</title>
            <link rel="stylesheet" href="/frontend/assets/css/print.css">
            <style>
                body { margin: 0; padding: 20px; }
            </style>
        </head>
        <body>
            <div id="printArea">
                <div class="kitchen-order">
                    <div class="kitchen-order-header">
                        <div>KITCHEN ORDER</div>
                        <div class="kitchen-table">TABLE ${order.table_number}</div>
                        <div style="font-size: 12pt;">${new Date(order.created_at).toLocaleString()}</div>
                    </div>
                    <div class="kitchen-items">
                        ${itemsList}
                    </div>
                    ${order.special_notes ? `
                        <div class="kitchen-notes" style="margin-top: 20px;">
                            <strong>SPECIAL INSTRUCTIONS:</strong><br>
                            ${order.special_notes}
                        </div>
                    ` : ''}
                    <div style="text-align: center; margin-top: 20px; font-size: 18pt;">
                        Order #${order.order_number}
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    // Close after printing
                    setTimeout(() => window.close(), 100);
                };
            </script>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
}

function printCustomerBill(order) {
    const printWindow = window.open('', '_blank');

    const itemsList = order.items.map(item => `
        <div class="receipt-item">
            <span class="item-name">${item.menu_item_name}</span>
            <span class="item-qty">${item.quantity}</span>
            <span class="item-price">${formatCurrency(item.price * item.quantity)}</span>
        </div>
        ${item.item_notes ? `<div class="item-notes">* ${item.item_notes}</div>` : ''}
    `).join('');

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Bill #${order.order_number}</title>
            <link rel="stylesheet" href="/frontend/assets/css/print.css">
            <script>
                function formatCurrency(amount) {
                    return '₹' + parseFloat(amount).toFixed(2);
                }
            </script>
        </head>
        <body>
            <div id="printArea">
                <div class="receipt">
                    <div class="receipt-header">
                        <h2>Obito Ani Foodzz</h2>
                        <div>123 Food Street, City</div>
                        <div>Phone: +91-1234567890</div>
                    </div>
                    <div class="receipt-info">
                        <div><span>Order #:</span><span>${order.order_number}</span></div>
                        <div><span>Table:</span><span>${order.table_number}</span></div>
                        <div><span>Date:</span><span>${new Date(order.created_at).toLocaleString()}</span></div>
                    </div>
                    <div class="receipt-items">
                        <div class="receipt-item" style="font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px;">
                            <span class="item-name">ITEM</span>
                            <span class="item-qty">QTY</span>
                            <span class="item-price">PRICE</span>
                        </div>
                        ${itemsList}
                    </div>
                    <div class="receipt-total">
                        <div class="total-row">
                            <span>TOTAL:</span>
                            <span>${formatCurrency(order.total_amount)}</span>
                        </div>
                    </div>
                    <div class="receipt-footer">
                        <div>Thank you for dining with us!</div>
                        <div>Please visit again</div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(() => window.close(), 100);
                };
            </script>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
}

// Print configuration
const PrintConfig = {
    autoPrint: false, // Auto-print new kitchen orders

    toggleAutoPrint() {
        this.autoPrint = !this.autoPrint;
        localStorage.setItem('auto_print_enabled', this.autoPrint);
        return this.autoPrint;
    },

    loadSettings() {
        this.autoPrint = localStorage.getItem('auto_print_enabled') === 'true';
    }
};

// Load settings on page load
if (typeof window !== 'undefined') {
    PrintConfig.loadSettings();
}
