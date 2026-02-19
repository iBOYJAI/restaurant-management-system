<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/includes/auth.php';
requireAnyRole(['chef', 'kitchen_staff', 'admin', 'manager', 'super_admin']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) - Restaurant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --kds-bg: #121212;
            --kds-card-bg: #1e1e1e;
            --kds-border: #333;
            --kds-text: #e0e0e0;
            --kds-accent: #ff9f43;
            /* Orange for attention */
            --kds-success: #28c76f;
            --kds-info: #00cfe8;
            --kds-danger: #ea5455;
        }

        body {
            background-color: var(--kds-bg);
            color: var(--kds-text);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            /* Prevent horizontal scroll */
        }

        /* Top Bar */
        .kds-header {
            background: var(--kds-card-bg);
            border-bottom: 1px solid var(--kds-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .kds-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(40, 199, 111, 0.15);
            color: var(--kds-success);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(40, 199, 111, 0.3);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--kds-success);
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(40, 199, 111, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(40, 199, 111, 0);
            }
        }

        /* Board Layout */
        .kds-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            padding: 1.5rem;
            margin-top: 70px;
            /* Offset header */
            height: calc(100vh - 70px);
        }

        .kds-column {
            background: #161616;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--kds-border);
            height: 100%;
            overflow: hidden;
        }

        .column-header {
            padding: 1rem;
            background: var(--kds-card-bg);
            border-bottom: 3px solid transparent;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .column-title {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            color: #ccc;
        }

        .column-count {
            background: #333;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* Column specific colors */
        .col-placed .column-header {
            border-bottom-color: var(--kds-info);
        }

        .col-placed .column-title {
            color: var(--kds-info);
        }

        .col-preparing .column-header {
            border-bottom-color: var(--kds-accent);
        }

        .col-preparing .column-title {
            color: var(--kds-accent);
        }

        .col-ready .column-header {
            border-bottom-color: var(--kds-success);
        }

        .col-ready .column-title {
            color: var(--kds-success);
        }

        .col-served .column-header {
            border-bottom-color: #666;
        }

        .column-body {
            padding: 1rem;
            overflow-y: auto;
            flex: 1;
            /* Scrollbar styling */
            scrollbar-width: thin;
            scrollbar-color: #444 #161616;
        }

        .column-body::-webkit-scrollbar {
            width: 6px;
        }

        .column-body::-webkit-scrollbar-thumb {
            background-color: #444;
            border-radius: 3px;
        }

        /* Order Card */
        .kds-card {
            background: var(--kds-card-bg);
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid var(--kds-border);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: move;
            /* Fallback */
            cursor: grab;
        }

        .kds-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-color: #555;
        }

        .kds-card:active {
            cursor: grabbing;
        }

        .card-header {
            padding: 12px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .order-id {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
        }

        .order-meta {
            font-size: 0.8rem;
            color: #999;
            margin-top: 4px;
        }

        .order-timer {
            font-family: monospace;
            font-size: 0.85rem;
            color: #888;
            background: #252525;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .card-body {
            padding: 12px;
        }

        .order-item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.95rem;
            color: #ddd;
            line-height: 1.4;
        }

        .item-qty {
            font-weight: 700;
            color: var(--kds-accent);
            margin-right: 8px;
            min-width: 20px;
        }

        .item-name {
            flex: 1;
        }

        .item-notes {
            display: block;
            font-size: 0.8rem;
            color: #e85d04;
            /* Orange-red for notes */
            margin-top: 2px;
            font-style: italic;
            padding-left: 28px;
        }

        .card-footer {
            padding: 12px;
            border-top: 1px solid #333;
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            justify-content: flex-end;
        }

        .kds-btn {
            background: #333;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            text-align: center;
        }

        .kds-btn:hover {
            filter: brightness(1.2);
        }

        .kds-btn.btn-next {
            background: var(--primary);
            color: white;
        }

        .kds-btn.btn-preparing {
            background: var(--kds-info);
            color: #000;
        }

        .kds-btn.btn-ready {
            background: var(--kds-accent);
            color: #000;
        }

        .kds-btn.btn-served {
            background: var(--kds-success);
            color: white;
        }

        /* Order Notes specific style */
        .special-note {
            background: rgba(234, 84, 85, 0.1);
            border-left: 3px solid var(--kds-danger);
            padding: 8px;
            margin: 8px 0;
            font-size: 0.85rem;
            color: #ffcccc;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .kds-board {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(2, 1fr);
                overflow-y: auto;
            }
        }

        @media (max-width: 768px) {
            .kds-board {
                display: flex;
                flex-direction: column;
                height: auto;
                overflow-y: visible;
            }

            .kds-column {
                min-height: 400px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="kds-header">
        <div class="kds-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
                <path d="M7 2v20"></path>
                <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path>
            </svg>
            KITCHEN DISPLAY
        </div>

        <div class="live-indicator">
            <div class="live-dot"></div>
            LIVE FEED
        </div>

        <div style="display: flex; gap: 10px;">
            <?php if (hasAnyRole(['admin', 'super_admin', 'manager'])): ?>
                <a href="../admin/dashboard.php" class="kds-btn" style="width: auto; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1);">Admin</a>
            <?php endif; ?>
            <a href="../index.php" class="kds-btn" style="width: auto; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1);">Menu</a>
            <a href="../admin/logout.php" class="kds-btn" style="width: auto; background: var(--kds-danger); color: white;">Logout</a>
        </div>
    </header>

    <!-- Main Board -->
    <div class="kds-board">
        <!-- New Orders -->
        <div class="kds-column col-placed" id="col-placed">
            <div class="column-header">
                <span class="column-title">New Orders</span>
                <span class="column-count" id="placed-count">0</span>
            </div>
            <div class="column-body" id="placed-orders">
                <!-- Orders injected via JS -->
            </div>
        </div>

        <!-- Preparing -->
        <div class="kds-column col-preparing" id="col-preparing">
            <div class="column-header">
                <span class="column-title">Preparing</span>
                <span class="column-count" id="preparing-count">0</span>
            </div>
            <div class="column-body" id="preparing-orders">
                <!-- Orders injected via JS -->
            </div>
        </div>

        <!-- Ready -->
        <div class="kds-column col-ready" id="col-ready">
            <div class="column-header">
                <span class="column-title">Ready to Serve</span>
                <span class="column-count" id="ready-count">0</span>
            </div>
            <div class="column-body" id="ready-orders">
                <!-- Orders injected via JS -->
            </div>
        </div>

        <!-- Completed -->
        <div class="kds-column col-served" id="col-served">
            <div class="column-header">
                <span class="column-title">Completed Orders</span>
                <span class="column-count" id="served-count">0</span>
            </div>
            <div class="column-body" id="served-orders">
                <!-- Orders injected via JS -->
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/kitchen.js"></script>
</body>

</html>