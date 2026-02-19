<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Obito Ani Foodzz</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        .review-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: transform var(--transition-base);
        }

        .review-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-sm);
        }

        .reviewer-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-primary);
        }

        .review-date {
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .stars {
            color: #ffd700;
            letter-spacing: 2px;
        }

        .review-text {
            color: var(--text-secondary);
            font-style: italic;
            line-height: 1.6;
        }

        .admin-reply {
            margin-top: var(--space-md);
            background: var(--bg-secondary);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            border-left: 3px solid var(--primary);
            font-size: 0.9rem;
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

    <!-- Hero Section -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container text-center">
            <h1>Customer Reviews</h1>
            <p>See what others are saying about our delicious food!</p>
        </div>
    </section>

    <!-- Reviews Grid -->
    <main class="container" style="max-width: 900px; padding-top: var(--space-xl); padding-bottom: var(--space-xl);">
        <div id="reviewsContainer">
            <!-- Loading State -->
            <div class="text-center p-4">
                <div class="loading">Loading reviews...</div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('reviewsContainer');

            try {
                // Fetch recent public reviews
                const response = await apiRequest('/backend/api/feedback.php?mode=recent');

                if (response.success && response.data.length > 0) {
                    container.innerHTML = '';

                    response.data.forEach(review => {
                        const stars = '★'.repeat(review.overall_rating) + '☆'.repeat(5 - review.overall_rating);
                        const date = new Date(review.created_at).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });

                        let replyHtml = '';
                        if (review.admin_response) {
                            replyHtml = `
                                <div class="admin-reply">
                                    <strong>Response from Restaurant:</strong><br>
                                    ${review.admin_response}
                                </div>
                            `;
                        }

                        const card = document.createElement('div');
                        card.className = 'review-card';
                        card.innerHTML = `
                            <div class="review-header">
                                <div class="reviewer-name">${review.customer_name}</div>
                                <div class="review-date">${date}</div>
                            </div>
                            <div class="stars mb-2">${stars}</div>
                            <div class="review-text">"${review.comments}"</div>
                            ${replyHtml}
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = `
                        <div class="text-center">
                            <h3>No reviews yet!</h3>
                            <p>Be the first to order and share your experience.</p>
                            <a href="index.php" class="btn btn-primary mt-3">Order Now</a>
                        </div>
                    `;
                }

                // Update cart count from existing logic
                updateCartBadge();

            } catch (error) {
                console.error(error);
                container.innerHTML = '<div class="alert alert-error">Failed to load reviews.</div>';
            }
        });

        function updateCartBadge() {
            // Simple reuse of the logic if Cart is globally available (it is in main.js)
            const count = Cart.getCount();
            const badge = document.querySelector('.nav-cart-count');
            if (badge) {
                badge.textContent = count;
                if (count > 0) badge.classList.remove('hidden');
                else badge.classList.add('hidden');
            }
        }
    </script>
</body>

</html>