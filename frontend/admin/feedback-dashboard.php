<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';

requireAnyRole(['super_admin', 'admin', 'manager']);
$admin = getCurrentAdmin();

// Fetch all feedback
require_once __DIR__ . '/../../backend/config/database.php';

$stmt = $pdo->query("
    SELECT f.*, o.order_number, o.table_number,
           u.full_name as responder_name
    FROM feedback f
    LEFT JOIN orders o ON f.order_id = o.id
    LEFT JOIN users u ON f.responded_by = u.id
    ORDER BY f.created_at DESC
");
$allFeedback = $stmt->fetchAll();

// Calculate averages
$avgStmt = $pdo->query("
    SELECT 
        AVG(overall_rating) as avg_overall,
        AVG(food_quality) as avg_food,
        AVG(service_rating) as avg_service,
        AVG(ambience_rating) as avg_ambience,
        COUNT(*) as total_feedback
    FROM feedback
");
$averages = $avgStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles are in includes/sidebar.php */

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: var(--space-lg);
            background: #f8fafc;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        .stat-card {
            background: white;
            padding: var(--space-lg);
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            letter-spacing: -1px;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .feedback-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: var(--space-lg);
        }

        .feedback-card {
            background: white;
            padding: var(--space-lg);
            border-radius: 24px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .feedback-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.08);
        }

        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feedback-card:hover::before {
            opacity: 1;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--space-md);
        }

        .customer-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .order-tag {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-top: 8px;
        }

        .rating-chip {
            background: #fffbeb;
            color: #b45309;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #fef3c7;
        }

        .metrics-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: var(--space-md) 0;
            padding: var(--space-md);
            background: #f8fafc;
            border-radius: 16px;
        }

        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .metric-label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        .metric-stars {
            color: #fbbf24;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .comment-text {
            color: #334155;
            font-size: 1rem;
            line-height: 1.6;
            margin: var(--space-sm) 0;
            flex-grow: 1;
            position: relative;
            padding-left: 20px;
        }

        .comment-text::before {
            content: '"';
            position: absolute;
            left: 0;
            top: -5px;
            font-size: 2rem;
            color: #e2e8f0;
            font-family: serif;
        }

        .date-footer {
            margin-top: var(--space-md);
            padding-top: var(--space-sm);
            border-top: 1px solid #f1f5f9;
            font-size: 0.75rem;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h1 class="mb-4" style="font-weight: 800; letter-spacing: -1px;">Customer Insights</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($averages['avg_overall'], 1) ?></div>
                    <div class="stat-label">Overall Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($averages['avg_food'], 1) ?></div>
                    <div class="stat-label">Food Quality</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($averages['avg_service'], 1) ?></div>
                    <div class="stat-label">Service</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($averages['avg_ambience'], 1) ?></div>
                    <div class="stat-label">Ambience</div>
                </div>
                <div class="stat-card" style="background: var(--primary); border: none;">
                    <div class="stat-value" style="color: black;"><?= $averages['total_feedback'] ?></div>
                    <div class="stat-label" style="color: rgba(0, 0, 0, 0.8);">Reviews</div>
                </div>
            </div>

            <div class="feedback-grid">
                <?php foreach ($allFeedback as $feedback): ?>
                    <div class="feedback-card">
                        <div class="feedback-header">
                            <div class="customer-info">
                                <h3><?= htmlspecialchars($feedback['customer_name']) ?></h3>
                                <?php if ($feedback['order_number']): ?>
                                    <span class="order-tag">Order #<?= $feedback['order_number'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="rating-chip">
                                <?= $feedback['overall_rating'] ?> <span style="font-size: 0.8rem;">★</span>
                            </div>
                        </div>

                        <div class="metrics-container">
                            <div class="metric-row">
                                <span class="metric-label">Food Quality</span>
                                <span class="metric-stars"><?= str_repeat('★', $feedback['food_quality'] ?? 0) . str_repeat('☆', 5 - ($feedback['food_quality'] ?? 0)) ?></span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Service</span>
                                <span class="metric-stars"><?= str_repeat('★', $feedback['service_rating'] ?? 0) . str_repeat('☆', 5 - ($feedback['service_rating'] ?? 0)) ?></span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Ambience</span>
                                <span class="metric-stars"><?= str_repeat('★', $feedback['ambience_rating'] ?? 0) . str_repeat('☆', 5 - ($feedback['ambience_rating'] ?? 0)) ?></span>
                            </div>
                        </div>

                        <?php if ($feedback['comments']): ?>
                            <div class="comment-text">
                                <?= htmlspecialchars($feedback['comments']) ?>
                            </div>
                        <?php else: ?>
                            <div style="flex-grow: 1;"></div>
                        <?php endif; ?>

                        <div class="date-footer">
                            <span>Posted on <?= date('M d, Y', strtotime($feedback['created_at'])) ?></span>
                            <span><?= date('g:i A', strtotime($feedback['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($allFeedback)): ?>
                <div style="text-align: center; padding: var(--space-xl); color: var(--text-secondary); grid-column: 1/-1;">
                    <img src="../assets/icons/chat.svg" style="width: 48px; opacity: 0.2; margin-bottom: 1rem;">
                    <h3>No feedback gathered yet</h3>
                    <p>Insights will appear here once customers start reviewing.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>