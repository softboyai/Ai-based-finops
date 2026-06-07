<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireRole('management');
$pageTitle = 'Management Dashboard';

// Key metrics
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
$totalBalance = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM customers WHERE status = 'active'")->fetchColumn();
$monthlyTransactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())")->fetchColumn();
$pendingAlerts = $pdo->query("SELECT COUNT(*) FROM risk_alerts WHERE reviewed = 0")->fetchColumn();
$loanAccounts = $pdo->query("SELECT COUNT(*) FROM customers WHERE account_type = 'loan' AND status = 'active'")->fetchColumn();
$missedPayments = $pdo->query("SELECT COUNT(*) FROM loan_repayments WHERE status = 'missed'")->fetchColumn();

// Monthly trend
$trend = getMonthlyTrend($pdo);
$monthlyData = getMonthlyTransactionData($pdo, 6);

include __DIR__ . '/../includes/header.php';
?>

<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:25px;">
    <h3 style="margin-bottom:8px;">Management Overview — Goshen Finance Plc</h3>
    <p style="color:var(--gray);font-size:0.9rem;">
        Welcome, <?= sanitize($_SESSION['name']) ?>. This dashboard provides a high-level overview of Goshen Finance Plc's 
        financial operations. Monitor performance metrics, AI risk alerts, and trends.
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Active Customers</h3>
        <div class="value"><?= number_format($totalCustomers) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Total Portfolio</h3>
        <div class="value"><?= formatCurrency($totalBalance) ?></div>
    </div>
    <div class="stat-card info">
        <h3>Monthly Transactions</h3>
        <div class="value"><?= number_format($monthlyTransactions) ?></div>
    </div>
    <div class="stat-card warning">
        <h3>Active Loans</h3>
        <div class="value"><?= number_format($loanAccounts) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Pending Risk Alerts</h3>
        <div class="value"><?= number_format($pendingAlerts) ?></div>
    </div>
    <div class="stat-card <?= $trend['change_percent'] >= 0 ? 'success' : 'danger' ?>">
        <h3>Monthly Trend</h3>
        <div class="value"><?= $trend['change_percent'] >= 0 ? '+' : '' ?><?= $trend['change_percent'] ?>%</div>
    </div>
</div>

<!-- Quick Navigation -->
<div class="chart-container" style="margin-bottom:25px;">
    <h3 style="margin-bottom:15px;">Quick Access</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/management/performance.php" class="btn btn-primary">📊 Monitor Performance</a>
        <a href="<?= BASE_URL ?>/reports/index.php" class="btn btn-info">📋 View Reports</a>
        <a href="<?= BASE_URL ?>/ai/insights.php" class="btn btn-success">🤖 AI Insights</a>
        <a href="<?= BASE_URL ?>/ai/risk_alerts.php" class="btn btn-danger">⚠️ Risk Alerts</a>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-container">
        <h3>🤖 Transaction Volume Trend</h3>
        <canvas id="volumeChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Deposits vs Withdrawals</h3>
        <canvas id="dvwChart"></canvas>
    </div>
</div>

<script>
const volCtx = document.getElementById('volumeChart').getContext('2d');
new Chart(volCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
        datasets: [{
            label: 'Total Volume (Rwf)',
            data: <?= json_encode(array_map(function($d) { return $d['deposits'] + $d['withdrawals'] + $d['repayments']; }, $monthlyData)) ?>,
            borderColor: '#003366',
            backgroundColor: 'rgba(0,51,102,0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

const dvwCtx = document.getElementById('dvwChart').getContext('2d');
new Chart(dvwCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
        datasets: [{
            label: 'Deposits',
            data: <?= json_encode(array_column($monthlyData, 'deposits')) ?>,
            backgroundColor: '#28a745'
        }, {
            label: 'Withdrawals',
            data: <?= json_encode(array_column($monthlyData, 'withdrawals')) ?>,
            backgroundColor: '#dc3545'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
