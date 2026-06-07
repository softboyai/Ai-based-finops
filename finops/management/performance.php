<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireRole(['admin', 'management']);
$pageTitle = 'Performance Monitor';

// === KPI METRICS ===

// Customer Growth
$customersThisMonth = $pdo->query("SELECT COUNT(*) FROM customers WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
$customersLastMonth = $pdo->query("SELECT COUNT(*) FROM customers WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))")->fetchColumn();
$customerGrowth = $customersLastMonth > 0 ? round(($customersThisMonth - $customersLastMonth) / $customersLastMonth * 100, 1) : 0;

// Transaction Volume
$transThisMonth = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())")->fetchColumn();
$transLastMonth = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE MONTH(date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))")->fetchColumn();
$transGrowth = $transLastMonth > 0 ? round(($transThisMonth - $transLastMonth) / $transLastMonth * 100, 1) : 0;

// Loan Repayment Rate
$totalRepayments = $pdo->query("SELECT COUNT(*) FROM loan_repayments WHERE status != 'pending'")->fetchColumn();
$paidRepayments = $pdo->query("SELECT COUNT(*) FROM loan_repayments WHERE status = 'paid'")->fetchColumn();
$repaymentRate = $totalRepayments > 0 ? round(($paidRepayments / $totalRepayments) * 100, 1) : 0;

// Risk Score Health
$highRisks = $pdo->query("SELECT COUNT(*) FROM risk_alerts WHERE risk_score = 'High' AND reviewed = 0")->fetchColumn();
$totalAlerts = $pdo->query("SELECT COUNT(*) FROM risk_alerts WHERE reviewed = 0")->fetchColumn();

// Officer Performance
$officerStats = $pdo->query("SELECT u.name, u.username, 
    COUNT(t.id) as transaction_count,
    COALESCE(SUM(t.amount), 0) as total_processed
    FROM users u 
    LEFT JOIN transactions t ON t.processed_by = u.id AND MONTH(t.date) = MONTH(NOW())
    WHERE u.role = 'finance_officer' AND u.status = 'active'
    GROUP BY u.id, u.name, u.username
    ORDER BY transaction_count DESC")->fetchAll();

// Account Type Distribution
$accountDist = $pdo->query("SELECT account_type, COUNT(*) as count, COALESCE(SUM(balance), 0) as total_balance 
                            FROM customers WHERE status = 'active' GROUP BY account_type ORDER BY count DESC")->fetchAll();

// Monthly performance data
$monthlyData = getMonthlyTransactionData($pdo, 6);

// Daily transactions this week
$dailyData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayLabel = date('D d/m', strtotime($date));
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE(date) = ?");
    $stmt->execute([$date]);
    $row = $stmt->fetch();
    $dailyData[] = ['day' => $dayLabel, 'count' => (int)$row['count'], 'total' => (float)$row['total']];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:25px;">
    <h3 style="margin-bottom:8px;">📊 Performance Monitor — Goshen Finance Plc</h3>
    <p style="color:var(--gray);font-size:0.9rem;">
        Real-time Key Performance Indicators (KPIs) for monitoring the financial health, 
        operational efficiency, and risk posture of Goshen Finance Plc. Data is AI-analyzed and updated live.
    </p>
</div>

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card <?= $customerGrowth >= 0 ? 'success' : 'danger' ?>">
        <h3>Customer Growth</h3>
        <div class="value"><?= $customerGrowth >= 0 ? '+' : '' ?><?= $customerGrowth ?>%</div>
        <small style="color:var(--gray);"><?= $customersThisMonth ?> new this month</small>
    </div>
    <div class="stat-card <?= $transGrowth >= 0 ? 'success' : 'danger' ?>">
        <h3>Transaction Volume Growth</h3>
        <div class="value"><?= $transGrowth >= 0 ? '+' : '' ?><?= $transGrowth ?>%</div>
        <small style="color:var(--gray);"><?= formatCurrency($transThisMonth) ?> this month</small>
    </div>
    <div class="stat-card <?= $repaymentRate >= 80 ? 'success' : ($repaymentRate >= 60 ? 'warning' : 'danger') ?>">
        <h3>Loan Repayment Rate</h3>
        <div class="value"><?= $repaymentRate ?>%</div>
        <small style="color:var(--gray);"><?= $paidRepayments ?> of <?= $totalRepayments ?> paid</small>
    </div>
    <div class="stat-card <?= $highRisks == 0 ? 'success' : 'danger' ?>">
        <h3>High Risk Alerts</h3>
        <div class="value"><?= $highRisks ?></div>
        <small style="color:var(--gray);"><?= $totalAlerts ?> total pending</small>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="chart-container">
        <h3>Daily Activity (Last 7 Days)</h3>
        <canvas id="dailyChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Account Type Distribution</h3>
        <canvas id="accountChart"></canvas>
    </div>
</div>

<!-- Officer Performance Table -->
<div class="table-container">
    <div class="table-header">
        <h3>Finance Officer Performance (This Month)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Officer Name</th>
                <th>Username</th>
                <th>Transactions Processed</th>
                <th>Total Amount Processed</th>
                <th>Performance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($officerStats as $officer): ?>
            <tr>
                <td><strong><?= sanitize($officer['name']) ?></strong></td>
                <td><?= sanitize($officer['username']) ?></td>
                <td><?= number_format($officer['transaction_count']) ?></td>
                <td><?= formatCurrency($officer['total_processed']) ?></td>
                <td>
                    <?php if ($officer['transaction_count'] >= 50): ?>
                        <span class="badge badge-low">Excellent</span>
                    <?php elseif ($officer['transaction_count'] >= 20): ?>
                        <span class="badge badge-medium">Good</span>
                    <?php elseif ($officer['transaction_count'] >= 1): ?>
                        <span class="badge badge-high">Needs Improvement</span>
                    <?php else: ?>
                        <span class="badge badge-high">No Activity</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($officerStats)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No finance officers registered yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Account Distribution Table -->
<div class="table-container">
    <div class="table-header">
        <h3>Portfolio by Account Type</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Account Type</th>
                <th>Number of Accounts</th>
                <th>Total Balance</th>
                <th>% of Portfolio</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalPortfolio = array_sum(array_column($accountDist, 'total_balance'));
            foreach ($accountDist as $acc): 
                $pct = $totalPortfolio > 0 ? round($acc['total_balance'] / $totalPortfolio * 100, 1) : 0;
            ?>
            <tr>
                <td><strong><?= ucfirst($acc['account_type']) ?></strong></td>
                <td><?= number_format($acc['count']) ?></td>
                <td><?= formatCurrency($acc['total_balance']) ?></td>
                <td><?= $pct ?>%</td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($accountDist)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--gray);">No account data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Performance Summary -->
<div class="chart-container" style="border-left:4px solid var(--success);margin-top:20px;">
    <h3 style="margin-bottom:10px;">🤖 AI Performance Assessment</h3>
    <div style="color:var(--gray);font-size:0.9rem;line-height:1.8;">
        <?php
        $issues = [];
        if ($repaymentRate < 70) $issues[] = "⚠️ Loan repayment rate is below 70% — review collection strategy";
        if ($highRisks > 5) $issues[] = "⚠️ More than 5 high-risk alerts pending — immediate review needed";
        if ($transGrowth < -20) $issues[] = "⚠️ Transaction volume dropped by more than 20% — investigate cause";
        if ($customerGrowth < 0) $issues[] = "⚠️ Customer base is shrinking — review acquisition strategy";

        if (empty($issues)) {
            echo "<p>✅ <strong>All KPIs are within healthy parameters.</strong> Operations running smoothly.</p>";
        } else {
            echo "<p><strong>Issues detected:</strong></p><ul style='margin-top:8px;padding-left:20px;'>";
            foreach ($issues as $issue) {
                echo "<li style='margin-bottom:5px;'>$issue</li>";
            }
            echo "</ul>";
        }
        ?>
        <p style="margin-top:10px;font-size:0.8rem;opacity:0.7;">
            Assessment generated by Goshen Finance AI Engine at <?= formatDateTime(date('Y-m-d H:i:s')) ?>
        </p>
    </div>
</div>

<script>
// Daily activity chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($dailyData, 'day')) ?>,
        datasets: [{
            label: 'Transactions',
            data: <?= json_encode(array_column($dailyData, 'count')) ?>,
            backgroundColor: '#003366'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

// Account type distribution
const accCtx = document.getElementById('accountChart').getContext('2d');
new Chart(accCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map(function($a) { return ucfirst($a['account_type']); }, $accountDist)) ?>,
        datasets: [{
            data: <?= json_encode(array_column($accountDist, 'count')) ?>,
            backgroundColor: ['#003366', '#28a745', '#ffc107', '#17a2b8']
        }]
    },
    options: { responsive: true }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
