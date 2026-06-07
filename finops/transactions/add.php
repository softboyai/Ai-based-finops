<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireRole(['admin', 'finance_officer']);
$pageTitle = 'Record Transaction';

$error = '';
$message = '';
$preselectedCustomer = (int)($_GET['customer_id'] ?? 0);

// Get active customers
$customers = $pdo->query("SELECT id, name, account_number, balance FROM customers WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if (!$customerId || !$type || $amount <= 0) {
        $error = 'Please fill all required fields with valid data.';
    } else {
        // Get customer
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? AND status = 'active'");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch();

        if (!$customer) {
            $error = 'Invalid or inactive customer account.';
        } elseif ($type === 'withdrawal' && $amount > $customer['balance']) {
            $error = 'Insufficient balance for this withdrawal. Current balance: ' . formatCurrency($customer['balance']);
        } else {
            try {
                $pdo->beginTransaction();

                // Insert transaction
                $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, type, amount, date, processed_by, notes) VALUES (?, ?, ?, NOW(), ?, ?)");
                $stmt->execute([$customerId, $type, $amount, $_SESSION['user_id'], $notes]);
                $transactionId = $pdo->lastInsertId();

                // Update customer balance
                if ($type === 'deposit' || $type === 'loan_repayment') {
                    $pdo->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?")->execute([$amount, $customerId]);
                } elseif ($type === 'withdrawal') {
                    $pdo->prepare("UPDATE customers SET balance = balance - ? WHERE id = ?")->execute([$amount, $customerId]);
                }

                $pdo->commit();

                // Run AI risk analysis
                $flags = analyzeTransaction($pdo, $transactionId);

                $message = 'Transaction recorded successfully.';
                if (!empty($flags)) {
                    $message .= ' <strong>AI Alert:</strong> ' . count($flags) . ' risk flag(s) detected.';
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Transaction failed: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>

<div class="form-container">
    <h3>Record New Transaction</h3>

    <?php if (empty($customers)): ?>
    <div class="alert alert-warning">
        <strong>No customers found!</strong> You need to add a customer before recording transactions.<br>
        <a href="<?= BASE_URL ?>/customers/add.php" style="color:var(--primary);font-weight:700;">👤 Click here to add a customer first →</a>
    </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Customer Account</label>
            <select name="customer_id" required>
                <option value="">Select Customer</option>
                <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $preselectedCustomer === $c['id'] ? 'selected' : '' ?>>
                    <?= sanitize($c['name']) ?> (<?= sanitize($c['account_number']) ?>) - Balance: <?= formatCurrency($c['balance']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Transaction Type</label>
                <select name="type" required>
                    <option value="">Select Type</option>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="loan_repayment">Loan Repayment</option>
                </select>
            </div>
            <div class="form-group">
                <label>Amount (Rwf)</label>
                <input type="number" step="1" min="1" name="amount" required class="currency-input">
            </div>
        </div>
        <div class="form-group">
            <label>Notes (optional)</label>
            <textarea name="notes" rows="3" placeholder="Transaction notes..."></textarea>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-success" style="width:auto;">Process Transaction</button>
            <a href="<?= BASE_URL ?>/transactions/index.php" class="btn btn-warning" style="width:auto;">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
