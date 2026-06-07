<?php
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            redirectByRole();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

function redirectByRole() {
    $base = BASE_URL;
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: $base/admin/dashboard.php");
            break;
        case 'finance_officer':
            header("Location: $base/finance/dashboard.php");
            break;
        case 'management':
            header("Location: $base/management/dashboard.php");
            break;
        default:
            header("Location: $base/auth/login.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Goshen Finance Plc | AI-Based FinOps MIS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .login-logo {
            display: block;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 3px solid #003366;
            box-shadow: 0 4px 15px rgba(0,51,102,0.2);
        }
        .ai-tag {
            display: inline-block;
            background: #003366;
            color: #fff;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <img src="<?= BASE_URL ?>/assets/images/goshen.png" alt="Goshen Finance Plc Logo" class="login-logo">
        <h1>Goshen Finance Plc</h1>
        <p class="subtitle">
            <span class="ai-tag">AI-POWERED</span> FinOps Management Information System
        </p>
        <p style="text-align:center;font-size:0.8rem;color:var(--gray);margin-bottom:20px;">
            Established 2005 | Authorized by MINICOM | Kigali, Rwanda
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required
                       value="<?= sanitize($username ?? '') ?>" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:0.75rem;color:var(--gray);">
            &copy; <?= date('Y') ?> Goshen Finance Plc. All rights reserved.
        </p>
    </div>
</div>
</body>
</html>
