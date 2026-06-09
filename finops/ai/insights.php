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

<!-- AI Algorithms Status Panel -->
<div class="chart-container" style="margin-bottom:20px;background:linear-gradient(135deg, #001a33 0%, #003366 100%);color:#fff;border:none;">
    <h3 style="color:#fff;margin-bottom:15px;">🧠 AI Engine Status — Active Algorithms</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:15px;">
        <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:15px;border:1px solid rgba(255,255,255,0.15);">
            <strong style="color:#4fc3f7;">Algorithm 1: Anomaly Detection</strong>
            <p style="font-size:0.8rem;opacity:0.8;margin-top:5px;">Flags transactions exceeding 3× the customer's historical average amount. Detects unusual large deposits or withdrawals.</p>
            <span style="background:#dc3545;padding:2px 8px;border-radius:3px;font-size:0.7rem;font-weight:700;">RISK: HIGH</span>
        </div>
        <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:15px;border:1px solid rgba(255,255,255,0.15);">
            <strong style="color:#4fc3f7;">Algorithm 2: Frequency Analysis</strong>
            <p style="font-size:0.8rem;opacity:0.8;margin-top:5px;">Detects 3+ transactions from the same account within a 1-hour window. Identifies rapid-fire suspicious activity.</p>
            <span style="background:#fd7e14;padding:2px 8px;border-radius:3px;font-size:0.7rem;font-weight:700;">RISK: MEDIUM</span>
        </div>
        <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:15px;border:1px solid rgba(255,255,255,0.15);">
            <strong style="color:#4fc3f7;">Algorithm 3: Balance Threshold</strong>
            <p style="font-size:0.8rem;opacity:0.8;margin-top:5px;">Monitors if account balance drops below 10% of the opening balance. Identifies accounts at financial risk.</p>
            <span style="background:#dc3545;padding:2px 8px;border-radius:3px;font-size:0.7rem;font-weight:700;">RISK: HIGH</span>
        </div>
        <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:15px;border:1px solid rgba(255,255,255,0.15);">
            <strong style="color:#4fc3f7;">Algorithm 4: Loan Default Prediction</strong>
            <p style="font-size:0.8rem;opacity:0.8;margin-top:5px;">Identifies customers with 2+ missed loan repayments. Predicts high probability of loan default.</p>
            <span style="background:#dc3545;padding:2px 8px;border-radius:3px;font-size:0.7rem;font-weight:700;">PREDICTION: DEFAULT</span>
        </div>
        <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:15px;border:1px solid rgba(255,255,255,0.15);">
            <strong style="color:#4fc3f7;">Algorithm 5: Trend Analysis</strong>
            <p style="font-size:0.8rem;opacity:0.8;margin-top:5px;">Compares monthly transaction volumes to detect growth or decline patterns. Calculates % change for forecasting.</p>
            <span style="background:#17a2b8;padding:2px 8px;border-radius:3px;font-size:0.7rem;font-weight:700;">ANALYSIS</span>
        </div>
        <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:15px;border:1px solid rgba(255,255,255,0.15);">
            <strong style="color:#4fc3f7;">Algorithm 6: Risk Scoring</strong>
            <p style="font-size:0.8rem;opacity:0.8;margin-top:5px;">Accumulates weighted scores per customer (High=3, Medium=2, Low=1). Ranks top 5 riskiest accounts.</p>
            <span style="background:#ffc107;color:#333;padding:2px 8px;border-radius:3px;font-size:0.7rem;font-weight:700;">SCORING</span>
        </div>
    </div>
    <p style="margin-top:15px;font-size:0.75rem;opacity:0.6;">AI Engine runs automatically after every transaction. No manual intervention required. Last analysis: <?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
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
