<?php
require_once 'includes/auth.php';

// If already logged in, go to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle form submission
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
    <title>Exam Cell Login — NBKRIST</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #1e40af;
            --dark-blue: #1e3a8a;
            --light-blue: #dbeafe;
            --lighter-blue: #f0f9ff;
            --border-blue: #bfdbfe;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
            --bg-light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--lighter-blue) 0%, var(--bg-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .login-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(30, 64, 175, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            border: 1px solid var(--border-blue);
        }

        .login-accent-bar {
            height: 5px;
            background: linear-gradient(90deg, var(--primary-blue), var(--border-blue));
        }

        .login-header {
            background: linear-gradient(135deg, var(--lighter-blue), var(--bg-light));
            color: var(--primary-blue);
            padding: 2.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border-blue);
        }

        .login-header .college-seal {
            width: 80px;
            height: 80px;
            background: var(--light-blue);
            border: 2px solid var(--border-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2.5rem;
            color: var(--primary-blue);
        }

        .login-header h4 {
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
            color: var(--primary-blue);
            font-size: 1.5rem;
        }

        .login-header p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0.5rem 0 0;
        }

        .login-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid var(--border-blue);
            border-radius: 8px;
            padding: 0.65rem 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
            outline: none;
        }

        .input-group-text {
            background: var(--lighter-blue);
            border: 1px solid var(--border-blue);
            color: var(--primary-blue);
            font-weight: 600;
        }

        .input-group .form-control {
            border-left: none;
        }

        .btn-login {
            background: var(--primary-blue);
            border: none;
            color: var(--white);
            width: 100%;
            padding: 0.8rem;
            font-weight: 600;
            border-radius: 8px;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .btn-login:hover {
            background: var(--dark-blue);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
        }

        .login-footer {
            text-align: center;
            padding: 1rem 2rem 1.5rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-blue);
        }

        .alert {
            border: 1px solid var(--border-blue);
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-accent-bar"></div>
    <div class="login-header">
        <div class="college-seal">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h4>NBKRIST</h4>
        <p>Examination Cell — Admin Portal</p>
    </div>

    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center py-2" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>
    </div>

    <div class="login-footer">
        &copy; <?php echo date('Y'); ?> NBKR Institute of Science &amp; Technology
    </div>
</div>

</body>
</html>
