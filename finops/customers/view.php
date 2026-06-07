<?php
require_once __DIR__ . '/../config/db.php';
requireRole(['admin', 'finance_officer']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: ' . BASE_URL . '/customers/index.php');
    exit;
}

$pageTitle = 'Customer: ' . $customer['name'];

// Get transactions
$stmt = $pdo->prepare("SELECT t.*, u.name as officer_name FROM transactions t 
                       JOIN users u ON t.processed_by = u.id 
                       WHERE t.customer_id = ? ORDER BY t.date DESC");
$stmt->execute([$id]);
$transactions = $stmt->fetchAll();

// Get risk alerts
$stmt = $pdo->prepare("SELECT * FROM risk_alerts WHERE customer_id = ? ORDER BY flagged_at DESC");
$stmt->execute([$id]);
$alerts = $stmt->fetchAll();

// Get loan repayments
$stmt = $pdo->prepare("SELECT * FROM loan_repayments WHERE customer_id = ? ORDER BY due_date DESC");
$stmt->execute([$id]);
$repayments = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Account Number</h3>
        <div class="value" style="font-size:1.3rem;"><?= sanitize($customer['account_number']) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Current Balance</h3>
        <div class="value"><?= formatCurrency($customer['balance']) ?></div>
    </div>
    <div class="stat-card info">
        <h3>Account Type</h3>
        <div class="value" style="font-size:1.3rem;"><?= ucfirst(str_replace('_', ' ', $customer['account_type'])) ?></div>
    </div>
    <div class="stat-card <?= $customer['status'] === 'active' ? 'success' : 'danger' ?>">
        <h3>Status</h3>
        <div class="value" style="font-size:1.3rem;"><?= ucfirst($customer['status']) ?></div>
    </div>
</div>

<div class="chart-container" style="margin-bottom:20px;padding:15px 20px;">
    <p style="color:var(--gray);font-size:0.9rem;">
        <strong>Date Opened:</strong> <?= formatDate($customer['date_opened']) ?> &nbsp;|&nbsp;
        <strong>Opening Balance:</strong> <?= formatCurrency($customer['opening_balance']) ?> &nbsp;|&nbsp;
        <strong>Account:</strong> <?= ucfirst($customer['account_type']) ?>
        <?php if ($customer['account_type'] === 'loan'): ?>
        &nbsp;|&nbsp; <span style="color:var(--danger);font-weight:600;">Loan Account — Managed by Goshen Finance Plc</span>
        <?php endif; ?>
    </p>
</div>

<div style="display:flex;gap:10px;margin-bottom:20px;">
    <a href="<?= BASE_URL ?>/customers/add.php?edit=<?= $customer['id'] ?>" class="btn btn-sm btn-warning">Edit Customer</a>
    <a href="<?= BASE_URL ?>/transactions/add.php?customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-success">New Transaction</a>
    <a href="<?= BASE_URL ?>/customers/index.php" class="btn btn-sm btn-info">Back to List</a>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Transaction History (<?= count($transactions) ?>)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Processed By</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= formatDateTime($t['date']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $t['type'])) ?></td>
                <td><?= formatCurrency($t['amount']) ?></td>
                <td><?= sanitize($t['officer_name']) ?></td>
                <td><?= sanitize($t['notes'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No transactions yet</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($alerts)): ?>
<div class="table-container">
    <div class="table-header">
        <h3>Risk Alerts (<?= count($alerts) ?>)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Risk Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alerts as $alert): ?>
            <tr class="risk-<?= strtolower($alert['risk_score']) ?>">
                <td><?= formatDateTime($alert['flagged_at']) ?></td>
                <td><?= sanitize($alert['flag_reason']) ?></td>
                <td><span class="badge badge-<?= strtolower($alert['risk_score']) ?>"><?= $alert['risk_score'] ?></span></td>
                <td><?= $alert['reviewed'] ? 'Reviewed' : 'Pending' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($repayments)): ?>
<div class="table-container">
    <div class="table-header">
        <h3>Loan Repayment Schedule (<?= count($repayments) ?>)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Due Date</th>
                <th>Amount Due</th>
                <th>Amount Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($repayments as $r): ?>
            <tr>
                <td><?= formatDate($r['due_date']) ?></td>
                <td><?= formatCurrency($r['amount_due']) ?></td>
                <td><?= formatCurrency($r['amount_paid']) ?></td>
                <td><span class="badge badge-<?= $r['status'] === 'paid' ? 'low' : ($r['status'] === 'missed' ? 'high' : 'medium') ?>"><?= ucfirst($r['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
