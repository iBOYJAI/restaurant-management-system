<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiter Login - Restaurant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-dark);
            color: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            background: var(--bg-dark);
            border: 1px solid var(--secondary);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            width: 100%;
            max-width: 400px;
            box-shadow: none;
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-lg);
        }

        .login-header h1 {
            color: var(--bg-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-sm);
        }

        .login-header h1 img {
            filter: invert(1);
            width: 32px;
            height: 32px;
        }

        .form-control {
            background: var(--bg-dark);
            border: 1px solid var(--secondary);
            color: var(--bg-primary);
        }

        .form-control:focus {
            border-color: var(--bg-primary);
            box-shadow: none;
        }

        .btn-primary {
            background: var(--bg-primary);
            color: var(--bg-dark);
            border: 1px solid var(--bg-primary);
            font-weight: 700;
        }

        .btn-primary:hover {
            background: transparent;
            color: var(--bg-primary);
            transform: none;
            box-shadow: none;
        }

        .back-link {
            color: var(--secondary);
            text-align: center;
            display: block;
            margin-top: var(--space-md);
            font-size: 0.875rem;
        }

        .back-link:hover {
            color: var(--bg-primary);
        }

        .quick-login-hint {
            margin-top: var(--space-md);
            padding: var(--space-sm);
            background: rgba(255,255,255,0.05);
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            color: var(--secondary);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <h1>🍽️ Waiter</h1>
            <p style="color: var(--secondary)">Staff Access Only</p>
        </div>

        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label class="form-label" style="color: var(--bg-primary)">Username</label>
                <input type="text" id="username" class="form-control" placeholder="e.g. waiter" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" style="color: var(--bg-primary)">Password</label>
                <input type="password" id="password" class="form-control" required>
            </div>

            <div id="errorMessage" class="alert alert-error" style="display: none; background: transparent; color: var(--danger); border-color: var(--danger);"></div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                SIGN IN
            </button>
        </form>

        <div class="quick-login-hint">
            <strong>Demo:</strong> Username <code>waiter</code>, Password <code>password</code>
        </div>

        <a href="../index.php" class="back-link">← Back to Menu</a>
        <a href="../admin/login.php" class="back-link">Admin / Manager / Chef login</a>
    </div>

    <script>
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
                const response = await fetch('../admin/login-handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const role = result.data ? result.data.role : '';
                    if (role === 'waiter' || role === 'manager' || role === 'admin') {
                        window.location.href = 'dashboard.php';
                    } else if (role === 'chef' || role === 'kitchen_staff') {
                        window.location.href = '../kitchen/dashboard.php';
                    } else {
                        window.location.href = '../admin/dashboard.php';
                    }
                } else {
                    errorDiv.textContent = result.message || 'Invalid credentials';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'Connection error';
                errorDiv.style.display = 'block';
            }
        }
    </script>
</body>

</html>
