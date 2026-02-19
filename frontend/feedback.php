<?php
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
// Allow access without order ID for testing/demo purposes, or redirect if strictly required.
// For now, we'll keep it as is but style the error state if needed.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Restaurant</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-secondary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-md);
        }

        .feedback-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .feedback-header {
            background: var(--gradient-primary);
            color: white;
            padding: var(--space-xl) var(--space-lg);
            text-align: center;
        }

        .feedback-header h1 {
            color: white;
            margin-bottom: var(--space-xs);
            font-size: 2rem;
        }

        .feedback-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .feedback-body {
            padding: var(--space-xl);
        }

        .rating-group {
            background: var(--bg-secondary);
            padding: var(--space-md);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-md);
            transition: transform var(--transition-fast);
        }

        .rating-group:hover {
            transform: translateY(-2px);
        }

        .rating-label {
            font-weight: 600;
            display: block;
            margin-bottom: var(--space-sm);
            color: var(--text-primary);
            font-family: var(--font-display);
        }

        .star-rating {
            display: flex;
            gap: 8px;
        }

        .star {
            font-size: 2rem;
            color: #d1d5db;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .star:hover,
        .star.active {
            color: #fbbf24;
            transform: scale(1.1);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        /* Success Animation */
        .success-animation {
            text-align: center;
            padding: var(--space-xl);
            display: none;
        }

        .checkmark-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--success);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--space-md);
            box-shadow: 0 10px 20px rgba(25, 135, 84, 0.3);
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .checkmark {
            color: white;
            font-size: 3rem;
        }

        @keyframes popIn {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>

    <div class="feedback-card">
        <div id="feedbackFormContainer">
            <div class="feedback-header">
                <h1>We Value Your Feedback</h1>
                <p>Tell us about your experience with order #<?= $orderId > 0 ? $orderId : 'Unknown' ?></p>
            </div>

            <div class="feedback-body">
                <?php if ($orderId == 0): ?>
                    <div class="alert alert-warning">
                        <strong>Note:</strong> No valid order ID provided. You can still submit general feedback.
                    </div>
                <?php endif; ?>

                <form id="feedbackForm" onsubmit="submitFeedback(event)">
                    <input type="hidden" id="orderId" value="<?= $orderId ?>">

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Your Name *</label>
                            <input type="text" id="customerName" class="form-control" required placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email (Optional)</label>
                            <input type="email" id="customerEmail" class="form-control" placeholder="john@example.com">
                        </div>
                    </div>

                    <div class="rating-group">
                        <label class="rating-label">Overall Experience *</label>
                        <div class="star-rating" id="overallRating" data-rating="0">
                            <span class="star" onclick="setRating('overall', 1)">★</span>
                            <span class="star" onclick="setRating('overall', 2)">★</span>
                            <span class="star" onclick="setRating('overall', 3)">★</span>
                            <span class="star" onclick="setRating('overall', 4)">★</span>
                            <span class="star" onclick="setRating('overall', 5)">★</span>
                        </div>
                    </div>

                    <div class="grid grid-3">
                        <div class="rating-group">
                            <label class="rating-label">Food Quality</label>
                            <div class="star-rating" id="foodRating" data-rating="0">
                                <span class="star" onclick="setRating('food', 1)">★</span>
                                <span class="star" onclick="setRating('food', 2)">★</span>
                                <span class="star" onclick="setRating('food', 3)">★</span>
                                <span class="star" onclick="setRating('food', 4)">★</span>
                                <span class="star" onclick="setRating('food', 5)">★</span>
                            </div>
                        </div>

                        <div class="rating-group">
                            <label class="rating-label">Service</label>
                            <div class="star-rating" id="serviceRating" data-rating="0">
                                <span class="star" onclick="setRating('service', 1)">★</span>
                                <span class="star" onclick="setRating('service', 2)">★</span>
                                <span class="star" onclick="setRating('service', 3)">★</span>
                                <span class="star" onclick="setRating('service', 4)">★</span>
                                <span class="star" onclick="setRating('service', 5)">★</span>
                            </div>
                        </div>

                        <div class="rating-group">
                            <label class="rating-label">Ambience</label>
                            <div class="star-rating" id="ambienceRating" data-rating="0">
                                <span class="star" onclick="setRating('ambience', 1)">★</span>
                                <span class="star" onclick="setRating('ambience', 2)">★</span>
                                <span class="star" onclick="setRating('ambience', 3)">★</span>
                                <span class="star" onclick="setRating('ambience', 4)">★</span>
                                <span class="star" onclick="setRating('ambience', 5)">★</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label class="form-label">Additional Comments</label>
                        <textarea id="comments" class="form-control" rows="4" placeholder="What did you like? What can we improve?"></textarea>
                    </div>

                    <div class="d-flex justify-between gap-3 mt-4">
                        <a href="index.php" class="btn btn-outline" style="flex: 1;">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="flex: 2;">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="successMessage" class="success-animation">
            <div class="checkmark-circle">
                <span class="checkmark">✓</span>
            </div>
            <h2 class="mb-2" style="color: var(--success);">Thank You!</h2>
            <p class="mb-4" style="color: var(--text-secondary);">Your feedback helps us serve you better.</p>
            <a href="index.php" class="btn btn-primary">Back to Menu</a>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function setRating(type, rating) {
            const containers = {
                overall: 'overallRating',
                food: 'foodRating',
                service: 'serviceRating',
                ambience: 'ambienceRating'
            };

            const container = document.getElementById(containers[type]);
            container.dataset.rating = rating;

            const stars = container.querySelectorAll('.star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        async function submitFeedback(event) {
            event.preventDefault();

            const overallRating = parseInt(document.getElementById('overallRating').dataset.rating);

            if (overallRating === 0) {
                showToast('Please provide an overall rating', 'error');
                return;
            }

            const data = {
                order_id: parseInt(document.getElementById('orderId').value),
                customer_name: document.getElementById('customerName').value,
                customer_email: document.getElementById('customerEmail').value,
                overall_rating: overallRating,
                food_quality: parseInt(document.getElementById('foodRating').dataset.rating) || null,
                service_rating: parseInt(document.getElementById('serviceRating').dataset.rating) || null,
                ambience_rating: parseInt(document.getElementById('ambienceRating').dataset.rating) || null,
                comments: document.getElementById('comments').value
            };

            try {
                const response = await apiRequest('/backend/api/feedback.php', 'POST', data);

                if (response.success) {
                    document.getElementById('feedbackFormContainer').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                } else {
                    showToast(response.message || 'Error submitting feedback', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An unexpected error occurred', 'error');
            }
        }
    </script>
</body>

</html>