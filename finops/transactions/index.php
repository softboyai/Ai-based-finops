<?php
require_once __DIR__ . '/../config/db.php';
requireRole(['admin', 'finance_officer']);
$pageTitle = 'Transactions';

// Filters
$filterType = $_GET['type'] ?? '';
$filterDate = $_GET['date'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = "1=1";
$params = [];

if ($filterType) {
    $where .= " AND t.type = ?";
    $params[] = $filterType;
}
if ($filterDate) {
    $where .= " AND DATE(t.date) = ?";
    $params[] = $filterDate;
}
if ($search) {
    $where .= " AND (c.name LIKE ? OR c.account_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT t.*, c.name as customer_name, c.account_number, u.name as officer_name
                        FROM transactions t 
                        JOIN customers c ON t.customer_id = c.id 
                        JOIN users u ON t.processed_by = u.id 
                        WHERE $where
                        ORDER BY t.date DESC LIMIT 100");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h3>Transaction Records</h3>
        <a href="<?= BASE_URL ?>/transactions/add.php" class="btn btn-sm btn-success">+ New Transaction</a>
    </div>
    <div style="padding:15px 20px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" placeholder="Customer name/account..." value="<?= sanitize($search) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:4px;">
            <select name="type" style="padding:8px 12px;border:1px solid var(--border);border-radius:4px;">
                <option value="">All Types</option>
                <option value="deposit" <?= $filterType === 'deposit' ? 'selected' : '' ?>>Deposit</option>
                <option value="withdrawal" <?= $filterType === 'withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                <option value="loan_repayment" <?= $filterType === 'loan_repayment' ? 'selected' : '' ?>>Loan Repayment</option>
            </select>
            <input type="date" name="date" value="<?= sanitize($filterDate) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:4px;">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="<?= BASE_URL ?>/transactions/index.php" class="btn btn-sm btn-warning">Clear</a>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Account #</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Processed By</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
            <tr>
                <td>#<?= $t['id'] ?></td>
                <td><?= formatDateTime($t['date']) ?></td>
                <td><?= sanitize($t['customer_name']) ?></td>
                <td><?= sanitize($t['account_number']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $t['type'])) ?></td>
                <td><?= formatCurrency($t['amount']) ?></td>
                <td><?= sanitize($t['officer_name']) ?></td>
                <td><?= sanitize($t['notes'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--gray);">No transactions found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
