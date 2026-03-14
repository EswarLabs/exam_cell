<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (loginAdmin($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NBKRIST Exam Cell</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #0052a3;
            --accent: #1a7fd4;
            --light: #f5f7fa;
            --lighter: #ecf0f5;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border: #e0e6ed;
            --white: #ffffff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --shadow-sm: 0 4px 14px rgba(0, 51, 102, 0.08);
            --shadow-md: 0 14px 36px rgba(0, 51, 102, 0.18);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            background: linear-gradient(135deg, #c7cacd 0%, #aab0b6 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-primary);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #003366 0%, #0052a3 100%);
            color: var(--white);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            right: -100px;
            top: -100px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .college-logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid var(--white);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            position: relative;
            z-index: 1;
        }

        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 15px 0 5px;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 35px;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            color: #721c24;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: var(--light);
            box-shadow: var(--shadow-sm);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(26, 127, 212, 0.1), var(--shadow-sm);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            pointer-events: none;
            z-index: 1;
        }

        .form-control.with-icon {
            padding-left: 45px;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            color: var(--white);
            width: 100%;
            padding: 14px;
            font-weight: 700;
            border-radius: 8px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #002244 0%, #004080 100%);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            background: var(--lighter);
            padding: 20px 35px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
            border-top: 1px solid var(--border);
        }

        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }

            .login-body {
                padding: 25px;
            }

            .login-header {
                padding: 30px 20px 20px;
            }

            .login-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="college-logo">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h2>NBKRIST</h2>
                <p>Examination Cell - Secure Admin Access</p>
            </div>

            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" autocomplete="off">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="bi bi-person"></i> Username
                        </label>
                        <div class="input-group">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" class="form-control with-icon" id="username" name="username"
                                placeholder="your username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <div class="input-group">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" class="form-control with-icon" id="password" name="password"
                                placeholder="your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <strong>NBKRIST</strong> | Exam Cell Administration<br>
                <small>&copy; <?php echo date('Y'); ?> All Rights Reserved</small>
            </div>
        </div>
    </div>

</body>

</html>