<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireLogin();
$pageTitle = 'AI Financial Insights — Goshen Finance Plc';

// Monthly trend
$trend = getMonthlyTrend($pdo);

// Top risk accounts
$topRisk = getTopRiskAccounts($pdo, 5);

// Loan default risk
$loanDefaults = getLoanDefaultRisk($pdo);

// Monthly data for charts
$monthlyData = getMonthlyTransactionData($pdo, 6);

include __DIR__ . '/../includes/header.php';
?>

<!-- AI Insights Header -->
<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:20px;padding:15px 20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h3 style="margin-bottom:5px;">🤖 AI-Powered Financial Insights</h3>
            <p style="color:var(--gray);font-size:0.85rem;">
                Goshen Finance Plc AI Engine analyzes transaction patterns, risk accumulation, and loan performance 
                to generate predictive insights and early warning indicators.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/reports/download.php?report=ai_insights" target="_blank" class="btn btn-sm btn-danger">📥 Download PDF</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card <?= $trend['change_percent'] >= 0 ? 'success' : 'danger' ?>">
        <h3>Monthly Trend</h3>
        <div class="value"><?= $trend['change_percent'] >= 0 ? '+' : '' ?><?= $trend['change_percent'] ?>%</div>
        <small>vs last month</small>
    </div>
    <div class="stat-card info">
        <h3>This Month Volume</h3>
        <div class="value"><?= formatCurrency($trend['current_month']) ?></div>
    </div>
    <div class="stat-card">
        <h3>Last Month Volume</h3>
        <div class="value"><?= formatCurrency($trend['last_month']) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Loan Default Risks</h3>
        <div class="value"><?= count($loanDefaults) ?></div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-container">
        <h3>Transaction Volume Trend (6 Months)</h3>
        <canvas id="volumeChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Deposits vs Withdrawals</h3>
        <canvas id="comparisonChart"></canvas>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Top 5 High-Risk Accounts</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Customer</th>
                <th>Account #</th>
                <th>Risk Score</th>
                <th>Alert Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topRisk as $i => $account): ?>
            <tr class="<?= $account['total_risk_score'] >= 9 ? 'risk-high' : ($account['total_risk_score'] >= 5 ? 'risk-medium' : 'risk-low') ?>">
                <td>#<?= $i + 1 ?></td>
                <td><?= sanitize($account['name']) ?></td>
                <td><?= sanitize($account['account_number']) ?></td>
                <td><strong><?= $account['total_risk_score'] ?></strong></td>
                <td><?= $account['alert_count'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topRisk)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No risk data available</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($loanDefaults)): ?>
<div class="table-container">
    <div class="table-header">
        <h3>Loan Default Risk (2+ Missed Repayments)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Account #</th>
                <th>Missed Payments</th>
                <th>Risk Level</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loanDefaults as $ld): ?>
            <tr class="risk-high">
                <td><?= sanitize($ld['name']) ?></td>
                <td><?= sanitize($ld['account_number']) ?></td>
                <td><?= $ld['missed_payments'] ?></td>
                <td><span class="badge badge-high">High</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
// Volume trend line chart
const volumeCtx = document.getElementById('volumeChart').getContext('2d');
new Chart(volumeCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
        datasets: [{
            label: 'Total Volume',
            data: <?= json_encode(array_map(function($d) { return $d['deposits'] + $d['withdrawals'] + $d['repayments']; }, $monthlyData)) ?>,
            borderColor: '#003366',
            backgroundColor: 'rgba(0, 51, 102, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Comparison bar chart
const compCtx = document.getElementById('comparisonChart').getContext('2d');
new Chart(compCtx, {
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
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
