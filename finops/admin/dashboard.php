<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireRole('admin');
$pageTitle = 'Admin Dashboard';

// Stats
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalTransactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())")->fetchColumn();
$totalBalance = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM customers WHERE status = 'active'")->fetchColumn();
$pendingAlerts = $pdo->query("SELECT COUNT(*) FROM risk_alerts WHERE reviewed = 0")->fetchColumn();
$loanAccounts = $pdo->query("SELECT COUNT(*) FROM customers WHERE account_type = 'loan' AND status = 'active'")->fetchColumn();

// Recent transactions
$recentTransactions = $pdo->query("SELECT t.*, c.name as customer_name, c.account_number, u.name as officer_name
                                    FROM transactions t 
                                    JOIN customers c ON t.customer_id = c.id 
                                    JOIN users u ON t.processed_by = u.id 
                                    ORDER BY t.date DESC LIMIT 10")->fetchAll();

// Recent alerts
$recentAlerts = $pdo->query("SELECT ra.*, c.name as customer_name, c.account_number
                              FROM risk_alerts ra 
                              JOIN customers c ON ra.customer_id = c.id 
                              ORDER BY ra.flagged_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Goshen Finance Welcome Card -->
<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:25px;">
    <h3 style="margin-bottom:10px;">Welcome to Goshen Finance Plc — FinOps Dashboard</h3>
    <p style="color:var(--gray);font-size:0.9rem;line-height:1.7;">
        <strong>Goshen Finance Plc</strong> was established in <strong>2005</strong> and is authorized by <strong>MINICOM</strong> (Ministry of Trade and Industry) 
        to provide financial services in Rwanda. Our core services include savings accounts, loan management, 
        investment products, and current accounts. This AI-powered FinOps Management Information System 
        supports intelligent risk detection, financial reporting, and operational efficiency for all departments.
    </p>
    <p style="color:var(--gray);font-size:0.85rem;margin-top:8px;">
        📍 Kigali, Rwanda &nbsp;|&nbsp; 📧 info@goshenfinance.rw &nbsp;|&nbsp; 📞 +250 788 000 000
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Customers</h3>
        <div class="value"><?= number_format($totalCustomers) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Monthly Transactions</h3>
        <div class="value"><?= number_format($totalTransactions) ?></div>
    </div>
    <div class="stat-card info">
        <h3>Total Balances</h3>
        <div class="value"><?= formatCurrency($totalBalance) ?></div>
    </div>
    <div class="stat-card warning">
        <h3>Active Loan Accounts</h3>
        <div class="value"><?= number_format($loanAccounts) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Pending Risk Alerts</h3>
        <div class="value"><?= number_format($pendingAlerts) ?></div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-container">
        <h3>🤖 AI Transaction Trends — Last 6 Months</h3>
        <canvas id="trendChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>🤖 AI Risk Alert Distribution</h3>
        <canvas id="riskChart"></canvas>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Recent Transactions</h3>
        <div style="display:flex;gap:8px;">
            <a href="<?= BASE_URL ?>/reports/download.php?report=monthly_summary" target="_blank" class="btn btn-sm btn-danger">📥 PDF</a>
            <a href="<?= BASE_URL ?>/transactions/index.php" class="btn btn-sm btn-primary">View All</a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Processed By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentTransactions as $t): ?>
            <tr>
                <td><?= formatDateTime($t['date']) ?></td>
                <td><?= sanitize($t['customer_name']) ?> (<?= sanitize($t['account_number']) ?>)</td>
                <td><?= ucfirst(str_replace('_', ' ', $t['type'])) ?></td>
                <td><?= formatCurrency($t['amount']) ?></td>
                <td><?= sanitize($t['officer_name']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentTransactions)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No transactions yet</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Recent Risk Alerts</h3>
        <a href="<?= BASE_URL ?>/ai/risk_alerts.php" class="btn btn-sm btn-danger">View All Alerts</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Risk Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentAlerts as $alert): ?>
            <tr class="risk-<?= strtolower($alert['risk_score']) ?>">
                <td><?= formatDateTime($alert['flagged_at']) ?></td>
                <td><?= sanitize($alert['customer_name']) ?></td>
                <td><?= sanitize($alert['flag_reason']) ?></td>
                <td><span class="badge badge-<?= strtolower($alert['risk_score']) ?>"><?= $alert['risk_score'] ?></span></td>
                <td><?= $alert['reviewed'] ? 'Reviewed' : 'Pending' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentAlerts)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No risk alerts</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Monthly trend chart
<?php $monthlyData = getMonthlyTransactionData($pdo); ?>
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
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
        }, {
            label: 'Loan Repayments',
            data: <?= json_encode(array_column($monthlyData, 'repayments')) ?>,
            backgroundColor: '#17a2b8'
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Risk distribution chart
<?php
$riskDist = $pdo->query("SELECT risk_score, COUNT(*) as count FROM risk_alerts GROUP BY risk_score")->fetchAll();
$riskLabels = array_column($riskDist, 'risk_score');
$riskCounts = array_column($riskDist, 'count');
?>
const riskCtx = document.getElementById('riskChart').getContext('2d');
new Chart(riskCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($riskLabels ?: ['No Data']) ?>,
        datasets: [{
            data: <?= json_encode($riskCounts ?: [1]) ?>,
            backgroundColor: ['#dc3545', '#ffc107', '#28a745']
        }]
    },
    options: { responsive: true }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
