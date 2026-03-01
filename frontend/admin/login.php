<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Restaurant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 50% 50%, #1a1a1a 0%, #000000 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-xl);
        }

        .login-logo {
            width: 64px;
            height: 64px;
            background: var(--gradient-primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-md);
            box-shadow: 0 10px 15px -3px rgba(255, 107, 53, 0.3);
        }

        .login-logo img {
            width: 32px;
            height: 32px;
            filter: brightness(0) invert(1);
        }

        h1 {
            color: white;
            font-size: 1.75rem;
            margin-bottom: var(--space-xs);
        }

        p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .form-control {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            margin-top: var(--space-sm);
        }

        .back-link {
            text-align: center;
            margin-top: var(--space-lg);
        }

        .back-link a {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: white;
        }

        .quick-login-badge {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .quick-login-badge:hover {
            background: rgba(255, 107, 53, 0.1);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.2);
        }

        .quick-login-badge:active {
            transform: scale(0.95);
        }

        .badge-icon {
            font-size: 1.25rem;
        }

        .badge-text {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 0.5px;
        }

        .quick-login-badge:hover .badge-text {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <img src="../assets/icons/lock.svg" alt="Lock">
            </div>
            <h1>Admin Panel</h1>
            <p>Enter your credentials to access the dashboard</p>
        </div>

        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" id="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div id="errorMessage" class="alert alert-danger" style="display: none; background: rgba(220, 53, 69, 0.1); border-color: rgba(220, 53, 69, 0.3); color: #ff6b6b;"></div>

            <button type="submit" class="btn btn-primary" style="margin-bottom: var(--space-md);">
                Sign In
            </button>

            <!-- Quick Login Access -->
            <div style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.25rem; color: rgba(255,255,255,0.4); text-align: center; font-weight: 700;">Quick Access (Demo)</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 12px;">
                    <button type="button" class="quick-login-badge" onclick="quickLogin('admin', 'admin123')">
                        <span class="badge-icon">👑</span>
                        <span class="badge-text">Admin</span>
                    </button>
                    <button type="button" class="quick-login-badge" onclick="quickLogin('manager', 'password')">
                        <span class="badge-icon">💼</span>
                        <span class="badge-text">Manager</span>
                    </button>
                    <button type="button" class="quick-login-badge" onclick="quickLogin('waiter', 'password')">
                        <span class="badge-icon">🍽️</span>
                        <span class="badge-text">Waiter</span>
                    </button>
                    <button type="button" class="quick-login-badge" onclick="quickLogin('chef', 'password')">
                        <span class="badge-icon">👨‍🍳</span>
                        <span class="badge-text">Chef</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="back-link">
            <a href="../index.php">← Back to Restaurant Menu</a>
        </div>
    </div>

    <script>
        async function quickLogin(u, p) {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            usernameInput.value = u;
            passwordInput.value = p;

            // Visual feedback on the inputs
            usernameInput.style.borderColor = 'var(--primary)';
            passwordInput.style.borderColor = 'var(--primary)';

            // Small delay for visual impact before submission
            setTimeout(() => {
                const eventMock = {
                    preventDefault: () => {}
                };
                handleLogin(eventMock);
            }, 300);
        }

        async function handleLogin(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');

            errorDiv.style.display = 'none';

            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            try {
                const response = await fetch('login-handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const role = result.data ? result.data.role : '';
                    if (role === 'chef' || role === 'kitchen_staff') {
                        window.location.href = '../kitchen/dashboard.php';
                    } else if (role === 'waiter') {
                        window.location.href = '../waiter/dashboard.php';
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                } else {
                    errorDiv.textContent = result.message || 'Invalid credentials';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'Error connecting to server';
                errorDiv.style.display = 'block';
            }
        }
    </script>
</body>

</html>