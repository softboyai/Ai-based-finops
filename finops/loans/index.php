<?php
require_once __DIR__ . '/../config/db.php';
requireRole(['admin', 'finance_officer']);
$pageTitle = 'Loan Management';

$message = '';
$error = '';

// Handle add repayment schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_schedule') {
        $customerId = (int)$_POST['customer_id'];
        $dueDate = $_POST['due_date'];
        $amountDue = floatval($_POST['amount_due']);

        if ($customerId && $dueDate && $amountDue > 0) {
            $stmt = $pdo->prepare("INSERT INTO loan_repayments (customer_id, due_date, amount_due, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$customerId, $dueDate, $amountDue]);
            $message = 'Repayment schedule added successfully.';
        } else {
            $error = 'Please fill all required fields.';
        }
    } elseif ($_POST['action'] === 'mark_paid') {
        $repaymentId = (int)$_POST['repayment_id'];
        $amountPaid = floatval($_POST['amount_paid']);
        $stmt = $pdo->prepare("SELECT * FROM loan_repayments WHERE id = ?");
        $stmt->execute([$repaymentId]);
        $repayment = $stmt->fetch();

        if ($repayment) {
            $status = $amountPaid >= $repayment['amount_due'] ? 'paid' : 'partial';
            $pdo->prepare("UPDATE loan_repayments SET amount_paid = ?, status = ? WHERE id = ?")
                ->execute([$amountPaid, $status, $repaymentId]);
            $message = 'Repayment recorded successfully.';
        }
    } elseif ($_POST['action'] === 'mark_missed') {
        $repaymentId = (int)$_POST['repayment_id'];
        $pdo->prepare("UPDATE loan_repayments SET status = 'missed' WHERE id = ?")->execute([$repaymentId]);
        $message = 'Marked as missed payment.';
    }
}

// Get loan customers
$loanCustomers = $pdo->query("SELECT id, name, account_number FROM customers WHERE account_type = 'loan' AND status = 'active' ORDER BY name")->fetchAll();

// Get all repayment schedules
$repayments = $pdo->query("SELECT lr.*, c.name as customer_name, c.account_number 
                           FROM loan_repayments lr 
                           JOIN customers c ON lr.customer_id = c.id 
                           ORDER BY lr.due_date DESC")->fetchAll();

// Stats
$totalDue = $pdo->query("SELECT COALESCE(SUM(amount_due), 0) FROM loan_repayments")->fetchColumn();
$totalPaid = $pdo->query("SELECT COALESCE(SUM(amount_paid), 0) FROM loan_repayments")->fetchColumn();
$missedCount = $pdo->query("SELECT COUNT(*) FROM loan_repayments WHERE status = 'missed'")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM loan_repayments WHERE status = 'pending'")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= sanitize($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>

<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:20px;padding:15px 20px;">
    <p style="color:var(--gray);font-size:0.9rem;">
        <strong>Goshen Finance Plc — Loan Management Module</strong><br>
        Loan management is a core service of Goshen Finance Plc. This module tracks all loan repayment schedules,
        missed payments, and default risk assessments for our lending portfolio.
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Loan Due</h3>
        <div class="value"><?= formatCurrency($totalDue) ?></div>
    </div>
    <div class="stat-card success">
        <h3>Total Repaid</h3>
        <div class="value"><?= formatCurrency($totalPaid) ?></div>
    </div>
    <div class="stat-card danger">
        <h3>Missed Payments</h3>
        <div class="value"><?= $missedCount ?></div>
    </div>
    <div class="stat-card warning">
        <h3>Pending Payments</h3>
        <div class="value"><?= $pendingCount ?></div>
    </div>
</div>

<!-- Add Repayment Schedule -->
<div class="form-container" style="max-width:100%;margin-bottom:30px;">
    <h3>Add Loan Repayment Schedule</h3>
    <form method="POST">
        <input type="hidden" name="action" value="add_schedule">
        <div class="form-row">
            <div class="form-group">
                <label>Loan Customer</label>
                <select name="customer_id" required>
                    <option value="">Select Loan Customer</option>
                    <?php foreach ($loanCustomers as $lc): ?>
                    <option value="<?= $lc['id'] ?>"><?= sanitize($lc['name']) ?> (<?= sanitize($lc['account_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" required>
            </div>
        </div>
        <div class="form-group" style="max-width:340px;">
            <label>Amount Due (Rwf)</label>
            <input type="number" step="1" min="1" name="amount_due" required>
        </div>
        <button type="submit" class="btn btn-success" style="width:auto;">Add Schedule</button>
    </form>
</div>

<!-- Repayment Records -->
<div class="table-container">
    <div class="table-header">
        <h3>Loan Repayment Records (<?= count($repayments) ?>)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Account #</th>
                <th>Due Date</th>
                <th>Amount Due</th>
                <th>Amount Paid</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($repayments as $r): ?>
            <tr class="<?= $r['status'] === 'missed' ? 'risk-high' : ($r['status'] === 'pending' ? 'risk-medium' : '') ?>">
                <td><?= sanitize($r['customer_name']) ?></td>
                <td><?= sanitize($r['account_number']) ?></td>
                <td><?= formatDate($r['due_date']) ?></td>
                <td><?= formatCurrency($r['amount_due']) ?></td>
                <td><?= formatCurrency($r['amount_paid']) ?></td>
                <td>
                    <span class="badge badge-<?= $r['status'] === 'paid' ? 'low' : ($r['status'] === 'missed' ? 'high' : 'medium') ?>">
                        <?= ucfirst($r['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="mark_paid">
                        <input type="hidden" name="repayment_id" value="<?= $r['id'] ?>">
                        <input type="number" name="amount_paid" value="<?= $r['amount_due'] ?>" style="width:100px;padding:4px;border:1px solid var(--border);border-radius:3px;" step="1" min="1">
                        <button type="submit" class="btn btn-sm btn-success">Pay</button>
                    </form>
                    <form method="POST" style="display:inline;margin-left:5px;">
                        <input type="hidden" name="action" value="mark_missed">
                        <input type="hidden" name="repayment_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Mark as missed?')">Missed</button>
                    </form>
                    <?php else: ?>
                    <span style="color:var(--gray);font-size:0.85rem;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($repayments)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--gray);">No repayment records. Add loan customers first.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
