<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireRole('finance_officer');
$pageTitle = 'Finance Officer Dashboard';

// Stats for this officer
$myTransactions = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE processed_by = ? AND MONTH(date) = MONTH(NOW())");
$myTransactions->execute([$_SESSION['user_id']]);
$myTransCount = $myTransactions->fetchColumn();

$todayTransactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(date) = CURDATE()")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
$pendingLoans = $pdo->query("SELECT COUNT(*) FROM loan_repayments WHERE status = 'pending'")->fetchColumn();
$totalDeposits = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'deposit' AND MONTH(date) = MONTH(NOW())")->fetchColumn();
$totalWithdrawals = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'withdrawal' AND MONTH(date) = MONTH(NOW())")->fetchColumn();

// Recent transactions by this officer
$stmt = $pdo->prepare("SELECT t.*, c.name as customer_name, c.account_number 
                        FROM transactions t JOIN customers c ON t.customer_id = c.id 
                        WHERE t.processed_by = ? ORDER BY t.date DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$recentTransactions = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:25px;">
    <h3 style="margin-bottom:8px;">Welcome, <?= sanitize($_SESSION['name']) ?></h3>
    <p style="color:var(--gray);font-size:0.9rem;">
        Finance Officer Dashboard — Goshen Finance Plc. From here you can process payments, manage customers,
        update financial records, and generate reports.
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>My Transactions (This Month)</h3>
        <div class="value"><?= number_format($myTransCount) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Today's Transactions</h3>
        <div class="value"><?= number_format($todayTransactions) ?></div>
    </div>
    <div class="stat-card info">
        <h3>Active Customers</h3>
        <div class="value"><?= number_format($totalCustomers) ?></div>
    </div>
    <div class="stat-card warning">
        <h3>Pending Loan Payments</h3>
        <div class="value"><?= number_format($pendingLoans) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Deposits (This Month)</h3>
        <div class="value"><?= formatCurrency($totalDeposits) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Withdrawals (This Month)</h3>
        <div class="value"><?= formatCurrency($totalWithdrawals) ?></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="chart-container" style="margin-bottom:25px;">
    <h3 style="margin-bottom:15px;">Quick Actions</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/transactions/add.php" class="btn btn-success">💳 Process Payment</a>
        <a href="<?= BASE_URL ?>/customers/add.php" class="btn btn-primary">👤 Add Customer</a>
        <a href="<?= BASE_URL ?>/loans/index.php" class="btn btn-warning">🏦 Manage Loans</a>
        <a href="<?= BASE_URL ?>/reports/generate.php" class="btn btn-info">📄 Generate Report</a>
        <a href="<?= BASE_URL ?>/finance/update_report.php" class="btn btn-primary">✏️ Update Financial Report</a>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>My Recent Transactions</h3>
        <a href="<?= BASE_URL ?>/transactions/index.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Account #</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentTransactions as $t): ?>
            <tr>
                <td><?= formatDateTime($t['date']) ?></td>
                <td><?= sanitize($t['customer_name']) ?></td>
                <td><?= sanitize($t['account_number']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $t['type'])) ?></td>
                <td><?= formatCurrency($t['amount']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentTransactions)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No transactions yet. Start by processing a payment.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
