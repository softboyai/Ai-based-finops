<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireLogin();
$pageTitle = 'Financial Reports';

$reportType = $_GET['report'] ?? 'monthly_summary';
$month = $_GET['month'] ?? date('Y-m');

// Monthly Transaction Summary
$monthStart = $month . '-01';
$monthEnd = date('Y-m-t', strtotime($monthStart));

$stmt = $pdo->prepare("SELECT type, COUNT(*) as count, SUM(amount) as total 
                        FROM transactions WHERE date >= ? AND date <= ? 
                        GROUP BY type");
$stmt->execute([$monthStart, $monthEnd . ' 23:59:59']);
$monthlySummary = $stmt->fetchAll();

// Account Balance Report
$balanceReport = $pdo->query("SELECT id, name, account_number, account_type, balance, opening_balance, status 
                              FROM customers WHERE status = 'active' ORDER BY balance DESC")->fetchAll();

// Income vs Expense
$stmt = $pdo->prepare("SELECT 
    COALESCE(SUM(CASE WHEN type = 'deposit' OR type = 'loan_repayment' THEN amount ELSE 0 END), 0) as income,
    COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as expense
    FROM transactions WHERE date >= ? AND date <= ?");
$stmt->execute([$monthStart, $monthEnd . ' 23:59:59']);
$incomeExpense = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<!-- Report Header / Branding -->
<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:20px;padding:15px 20px;">
    <p style="font-weight:700;color:var(--primary);font-size:1.1rem;margin-bottom:5px;">Goshen Finance Plc — Confidential Financial Report</p>
    <p style="color:var(--gray);font-size:0.85rem;">
        Authorized by MINICOM | Established 2005 | Kigali, Rwanda<br>
        Report generated: <?= formatDateTime(date('Y-m-d H:i:s')) ?> by <?= sanitize($_SESSION['name']) ?>
    </p>
</div>

<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="?report=monthly_summary&month=<?= $month ?>" class="btn btn-sm <?= $reportType === 'monthly_summary' ? 'btn-primary' : 'btn-info' ?>">Monthly Summary</a>
    <a href="?report=balance&month=<?= $month ?>" class="btn btn-sm <?= $reportType === 'balance' ? 'btn-primary' : 'btn-info' ?>">Account Balances</a>
    <a href="?report=income_expense&month=<?= $month ?>" class="btn btn-sm <?= $reportType === 'income_expense' ? 'btn-primary' : 'btn-info' ?>">Income vs Expense</a>
    <a href="?report=loans&month=<?= $month ?>" class="btn btn-sm <?= $reportType === 'loans' ? 'btn-primary' : 'btn-info' ?>">Loan Portfolio</a>
    <form method="GET" style="display:inline-flex;gap:5px;align-items:center;">
        <input type="hidden" name="report" value="<?= sanitize($reportType) ?>">
        <input type="month" name="month" value="<?= sanitize($month) ?>" style="padding:6px 10px;border:1px solid var(--border);border-radius:4px;">
        <button type="submit" class="btn btn-sm btn-success">Go</button>
    </form>
    <a href="<?= BASE_URL ?>/reports/download.php?report=<?= $reportType ?>&month=<?= $month ?>" target="_blank" class="btn btn-sm btn-danger">📥 Download PDF</a>
    <button onclick="printReport()" class="btn btn-sm btn-warning">🖨️ Print</button>
</div>

<?php if ($reportType === 'monthly_summary'): ?>
<div class="table-container">
    <div class="table-header">
        <h3>Monthly Transaction Summary — <?= date('F Y', strtotime($monthStart)) ?></h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Transaction Type</th>
                <th>Count</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grandTotal = 0;
            foreach ($monthlySummary as $row): 
                $grandTotal += $row['total'];
            ?>
            <tr>
                <td><?= ucfirst(str_replace('_', ' ', $row['type'])) ?></td>
                <td><?= number_format($row['count']) ?></td>
                <td><?= formatCurrency($row['total']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($monthlySummary)): ?>
            <tr><td colspan="3" style="text-align:center;color:var(--gray);">No transactions this month</td></tr>
            <?php else: ?>
            <tr style="font-weight:bold;background:var(--light-gray);">
                <td>TOTAL</td>
                <td>—</td>
                <td><?= formatCurrency($grandTotal) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if ($reportType === 'balance'): ?>
<div class="table-container">
    <div class="table-header">
        <h3>Account Balance Report — Goshen Finance Plc</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Account #</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Opening Balance</th>
                <th>Current Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($balanceReport as $row): ?>
            <tr>
                <td><?= sanitize($row['account_number']) ?></td>
                <td><?= sanitize($row['name']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $row['account_type'])) ?></td>
                <td><?= formatCurrency($row['opening_balance']) ?></td>
                <td><?= formatCurrency($row['balance']) ?></td>
                <td><span class="badge badge-active"><?= ucfirst($row['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($balanceReport)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--gray);">No active accounts</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if ($reportType === 'income_expense'): ?>
<div class="stats-grid">
    <div class="stat-card success">
        <h3>Income (Deposits + Repayments)</h3>
        <div class="value"><?= formatCurrency($incomeExpense['income']) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Expense (Withdrawals)</h3>
        <div class="value"><?= formatCurrency($incomeExpense['expense']) ?></div>
    </div>
    <div class="stat-card info">
        <h3>Net Position</h3>
        <div class="value"><?= formatCurrency($incomeExpense['income'] - $incomeExpense['expense']) ?></div>
    </div>
</div>

<div class="chart-container">
    <h3>Income vs Expense — <?= date('F Y', strtotime($monthStart)) ?></h3>
    <canvas id="incomeExpenseChart" style="max-height:300px;"></canvas>
</div>

<script>
const ieCtx = document.getElementById('incomeExpenseChart').getContext('2d');
new Chart(ieCtx, {
    type: 'bar',
    data: {
        labels: ['Income', 'Expense', 'Net Position'],
        datasets: [{
            label: 'Amount (Rwf)',
            data: [<?= $incomeExpense['income'] ?>, <?= $incomeExpense['expense'] ?>, <?= $incomeExpense['income'] - $incomeExpense['expense'] ?>],
            backgroundColor: ['#28a745', '#dc3545', '#17a2b8']
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<?php endif; ?>

<?php if ($reportType === 'loans'): ?>
<?php
// Loan portfolio report
$loanCustomers = $pdo->query("SELECT c.*, 
    (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'paid') as paid_count,
    (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'missed') as missed_count,
    (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'pending') as pending_count,
    (SELECT COALESCE(SUM(lr.amount_due), 0) FROM loan_repayments lr WHERE lr.customer_id = c.id) as total_due,
    (SELECT COALESCE(SUM(lr.amount_paid), 0) FROM loan_repayments lr WHERE lr.customer_id = c.id) as total_paid
    FROM customers c WHERE c.account_type = 'loan' AND c.status = 'active' ORDER BY c.name")->fetchAll();
?>
<div class="stats-grid">
    <div class="stat-card">
        <h3>Active Loan Accounts</h3>
        <div class="value"><?= count($loanCustomers) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Total Loan Portfolio</h3>
        <div class="value"><?= formatCurrency(array_sum(array_column($loanCustomers, 'total_due'))) ?></div>
    </div>
    <div class="stat-card info">
        <h3>Total Repaid</h3>
        <div class="value"><?= formatCurrency(array_sum(array_column($loanCustomers, 'total_paid'))) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Missed Payments</h3>
        <div class="value"><?= array_sum(array_column($loanCustomers, 'missed_count')) ?></div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Loan Portfolio — Goshen Finance Plc</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Account #</th>
                <th>Total Due</th>
                <th>Total Paid</th>
                <th>Paid</th>
                <th>Missed</th>
                <th>Pending</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loanCustomers as $lc): ?>
            <tr class="<?= $lc['missed_count'] >= 2 ? 'risk-high' : ($lc['missed_count'] >= 1 ? 'risk-medium' : '') ?>">
                <td><?= sanitize($lc['name']) ?></td>
                <td><?= sanitize($lc['account_number']) ?></td>
                <td><?= formatCurrency($lc['total_due']) ?></td>
                <td><?= formatCurrency($lc['total_paid']) ?></td>
                <td><?= $lc['paid_count'] ?></td>
                <td><?= $lc['missed_count'] ?></td>
                <td><?= $lc['pending_count'] ?></td>
                <td>
                    <?php if ($lc['missed_count'] >= 2): ?>
                        <span class="badge badge-high">Default Risk</span>
                    <?php elseif ($lc['missed_count'] >= 1): ?>
                        <span class="badge badge-medium">Watch</span>
                    <?php else: ?>
                        <span class="badge badge-low">Good</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($loanCustomers)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--gray);">No active loan accounts</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Print Footer -->
<div style="display:none;" class="print-footer">
    <hr style="margin:30px 0 10px;">
    <p style="text-align:center;font-size:0.8rem;color:var(--gray);">
        Goshen Finance Plc — Confidential Financial Report<br>
        Authorized by MINICOM | Est. 2005 | Kigali, Rwanda<br>
        Generated: <?= formatDateTime(date('Y-m-d H:i:s')) ?>
    </p>
</div>

<style>
@media print {
    .print-footer { display: block !important; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
