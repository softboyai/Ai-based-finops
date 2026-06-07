<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$base = BASE_URL;
?>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="<?= $base ?>/assets/images/goshen.png" alt="Logo" style="width:50px;height:50px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid rgba(255,255,255,0.3);">
        <h2>Goshen Finance Plc</h2>
        <small>AI-Based FinOps MIS</small>
        <small style="display:block;opacity:0.5;font-size:0.7rem;margin-top:3px;">Est. 2005 | Authorized by MINICOM</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="<?= $base ?>/admin/dashboard.php" class="<?= $currentPage === 'dashboard.php' && $currentDir === 'admin' ? 'active' : '' ?>">
            <span class="icon">&#9776;</span> Dashboard
        </a>
        <?php elseif ($_SESSION['role'] === 'finance_officer'): ?>
        <a href="<?= $base ?>/finance/dashboard.php" class="<?= $currentPage === 'dashboard.php' && $currentDir === 'finance' ? 'active' : '' ?>">
            <span class="icon">&#9776;</span> Dashboard
        </a>
        <?php else: ?>
        <a href="<?= $base ?>/management/dashboard.php" class="<?= $currentPage === 'dashboard.php' && $currentDir === 'management' ? 'active' : '' ?>">
            <span class="icon">&#9776;</span> Dashboard
        </a>
        <?php endif; ?>

        <?php if (in_array($_SESSION['role'], ['admin', 'finance_officer'])): ?>
        <div class="nav-section">Operations</div>
        <a href="<?= $base ?>/customers/index.php" class="<?= $currentDir === 'customers' ? 'active' : '' ?>">
            <span class="icon">&#128100;</span> Customers
        </a>
        <a href="<?= $base ?>/transactions/index.php" class="<?= $currentDir === 'transactions' ? 'active' : '' ?>">
            <span class="icon">&#128176;</span> Transactions
        </a>
        <a href="<?= $base ?>/transactions/add.php" class="<?= $currentPage === 'add.php' && $currentDir === 'transactions' ? 'active' : '' ?>">
            <span class="icon">&#128179;</span> Process Payment
        </a>
        <a href="<?= $base ?>/loans/index.php" class="<?= $currentDir === 'loans' ? 'active' : '' ?>">
            <span class="icon">&#128181;</span> Loan Management
        </a>
        <?php endif; ?>

        <?php if (in_array($_SESSION['role'], ['admin', 'finance_officer'])): ?>
        <div class="nav-section">Reporting</div>
        <a href="<?= $base ?>/reports/index.php" class="<?= $currentDir === 'reports' && $currentPage === 'index.php' ? 'active' : '' ?>">
            <span class="icon">&#128202;</span> Financial Reports
        </a>
        <a href="<?= $base ?>/reports/generate.php" class="<?= $currentPage === 'generate.php' ? 'active' : '' ?>">
            <span class="icon">&#128196;</span> Generate Report
        </a>
        <a href="<?= $base ?>/finance/update_report.php" class="<?= $currentPage === 'update_report.php' ? 'active' : '' ?>">
            <span class="icon">&#9999;</span> Update Report
        </a>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'management'): ?>
        <div class="nav-section">Reporting</div>
        <a href="<?= $base ?>/reports/index.php" class="<?= $currentDir === 'reports' && $currentPage === 'index.php' ? 'active' : '' ?>">
            <span class="icon">&#128202;</span> Financial Reports
        </a>
        <a href="<?= $base ?>/management/performance.php" class="<?= $currentPage === 'performance.php' ? 'active' : '' ?>">
            <span class="icon">&#128200;</span> Monitor Performance
        </a>
        <?php endif; ?>

        <div class="nav-section">AI Analytics</div>
        <a href="<?= $base ?>/ai/insights.php" class="<?= $currentDir === 'ai' && $currentPage === 'insights.php' ? 'active' : '' ?>">
            <span class="icon">&#129302;</span> AI Insights
        </a>
        <a href="<?= $base ?>/ai/risk_alerts.php" class="<?= $currentDir === 'ai' && $currentPage === 'risk_alerts.php' ? 'active' : '' ?>">
            <span class="icon">&#9888;</span> Risk Alerts
        </a>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="nav-section">Administration</div>
        <a href="<?= $base ?>/admin/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
            <span class="icon">&#128101;</span> Users
        </a>
        <a href="<?= $base ?>/admin/settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <span class="icon">&#9881;</span> Settings
        </a>
        <?php endif; ?>
    </nav>
</div>
