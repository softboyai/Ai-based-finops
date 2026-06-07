<?php
session_start();

// Auto-detect base path
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base = $scriptDir;

// If logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin': header("Location: $base/admin/dashboard.php"); break;
        case 'finance_officer': header("Location: $base/finance/dashboard.php"); break;
        case 'management': header("Location: $base/management/dashboard.php"); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goshen Finance Plc — AI-Based FinOps Management Information System</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <style>
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #003366 0%, #004080 50%, #001a33 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
        }
        .hero-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
        }
        .hero-nav .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .hero-nav .logo-area img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .hero-nav .logo-area h2 {
            font-size: 1.3rem;
        }
        .hero-nav .logo-area small {
            display: block;
            opacity: 0.7;
            font-size: 0.75rem;
        }
        .hero-nav a.login-btn {
            background: #fff;
            color: #003366;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }
        .hero-nav a.login-btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }
        .hero-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 60px;
        }
        .hero-content {
            max-width: 700px;
            text-align: center;
        }
        .hero-content .logo-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.2);
            margin-bottom: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .hero-content h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            font-weight: 800;
        }
        .hero-content .tagline {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        .hero-content .auth-info {
            font-size: 0.9rem;
            opacity: 0.7;
            margin-bottom: 35px;
        }
        .hero-content p.description {
            font-size: 1rem;
            line-height: 1.8;
            opacity: 0.85;
            margin-bottom: 40px;
        }
        .hero-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .hero-feature {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 25px 15px;
            text-align: center;
        }
        .hero-feature .icon {
            font-size: 2.2rem;
            margin-bottom: 10px;
            display: block;
        }
        .hero-feature h4 {
            font-size: 0.95rem;
            margin-bottom: 6px;
        }
        .hero-feature p {
            font-size: 0.8rem;
            opacity: 0.7;
            line-height: 1.5;
        }
        .hero-cta {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .hero-cta a {
            padding: 15px 35px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .hero-cta a.primary {
            background: #fff;
            color: #003366;
        }
        .hero-cta a.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .hero-cta a.secondary {
            border: 2px solid rgba(255,255,255,0.5);
            color: #fff;
        }
        .hero-cta a.secondary:hover {
            border-color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .hero-footer {
            text-align: center;
            padding: 20px;
            opacity: 0.5;
            font-size: 0.8rem;
        }
        @media (max-width: 768px) {
            .hero-nav { padding: 15px 20px; }
            .hero-main { padding: 20px; }
            .hero-content h1 { font-size: 2rem; }
            .hero-features { grid-template-columns: 1fr; }
            .hero-cta { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="hero">
    <div class="hero-nav">
        <div class="logo-area">
            <img src="<?= $base ?>/assets/images/goshen.png" alt="Goshen Finance Plc Logo">
            <div>
                <h2>Goshen Finance Plc</h2>
                <small>Authorized by MINICOM</small>
            </div>
        </div>
        <a href="<?= $base ?>/auth/login.php" class="login-btn">Sign In</a>
    </div>

    <div class="hero-main">
        <div class="hero-content">
            <img src="<?= $base ?>/assets/images/goshen.png" alt="Goshen Finance Plc" class="logo-large">
            <h1>Goshen Finance Plc</h1>
            <p class="tagline">AI-Based FinOps Management Information System</p>
            <p class="auth-info">Established 2005 | Authorized by MINICOM | Kigali, Rwanda</p>
            <p class="description">
                An intelligent financial operations platform powered by AI-driven risk detection and analytics.
                Goshen Finance Plc leverages machine-learning-inspired algorithms to monitor transactions,
                detect anomalies, assess loan default risks, and generate actionable financial insights —
                all built to serve our customers in Rwanda with excellence and security.
            </p>

            <div class="hero-features">
                <div class="hero-feature">
                    <span class="icon">🤖</span>
                    <h4>AI Risk Detection</h4>
                    <p>Real-time transaction anomaly detection with automated risk scoring</p>
                </div>
                <div class="hero-feature">
                    <span class="icon">📊</span>
                    <h4>AI Financial Insights</h4>
                    <p>Predictive trends, loan default risk analysis & smart reporting</p>
                </div>
                <div class="hero-feature">
                    <span class="icon">💰</span>
                    <h4>Loan Management</h4>
                    <p>Full loan lifecycle tracking with AI-powered default prediction</p>
                </div>
            </div>

            <div class="hero-cta">
                <a href="<?= $base ?>/auth/login.php" class="primary">Access System</a>
            </div>
        </div>
    </div>

    <div class="hero-footer">
        &copy; <?= date('Y') ?> Goshen Finance Plc. All rights reserved. | AI-Based FinOps MIS v1.0
    </div>
</div>
</body>
</html>
