<?php
require_once __DIR__ . '/../config/db.php';
requireRole(['admin', 'finance_officer']);

$editCustomer = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCustomer = $stmt->fetch();
}

$pageTitle = $editCustomer ? 'Edit Customer' : 'Add Customer';
$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $accountNumber = trim($_POST['account_number'] ?? '');
    $accountType = $_POST['account_type'] ?? '';
    $balance = floatval($_POST['balance'] ?? 0);
    $dateOpened = $_POST['date_opened'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'active';
    $customerId = $_POST['customer_id'] ?? '';

    if (empty($name) || empty($accountNumber) || empty($accountType)) {
        $error = 'Name, account number, and account type are required.';
    } else {
        if ($customerId) {
            // Edit
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, account_number = ?, account_type = ?, balance = ?, date_opened = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $accountNumber, $accountType, $balance, $dateOpened, $status, $customerId]);
            $message = 'Customer updated successfully.';
            // Refresh edit data
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$customerId]);
            $editCustomer = $stmt->fetch();
        } else {
            // Check unique account number
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE account_number = ?");
            $stmt->execute([$accountNumber]);
            if ($stmt->fetch()) {
                $error = 'Account number already exists.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO customers (name, account_number, account_type, balance, opening_balance, date_opened, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $accountNumber, $accountType, $balance, $balance, $dateOpened, $status]);
                $message = 'Customer added successfully.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= sanitize($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>

<div class="form-container">
    <h3><?= $editCustomer ? 'Edit Customer' : 'Add New Customer' ?></h3>
    <form method="POST">
        <?php if ($editCustomer): ?>
            <input type="hidden" name="customer_id" value="<?= $editCustomer['id'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required value="<?= sanitize($editCustomer['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Account Number</label>
                <input type="text" name="account_number" required value="<?= sanitize($editCustomer['account_number'] ?? '') ?>"
                       <?= $editCustomer ? 'readonly' : '' ?>>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Account Type</label>
                <select name="account_type" required>
                    <option value="">Select Type</option>
                    <option value="savings" <?= ($editCustomer['account_type'] ?? '') === 'savings' ? 'selected' : '' ?>>Savings</option>
                    <option value="loan" <?= ($editCustomer['account_type'] ?? '') === 'loan' ? 'selected' : '' ?>>Loan</option>
                    <option value="investment" <?= ($editCustomer['account_type'] ?? '') === 'investment' ? 'selected' : '' ?>>Investment</option>
                    <option value="current" <?= ($editCustomer['account_type'] ?? '') === 'current' ? 'selected' : '' ?>>Current</option>
                </select>
            </div>
            <div class="form-group">
                <label>Opening Balance (Rwf)</label>
                <input type="number" step="1" name="balance" class="currency-input" value="<?= $editCustomer['balance'] ?? '0' ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Date Opened</label>
                <input type="date" name="date_opened" value="<?= $editCustomer['date_opened'] ?? date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= ($editCustomer['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($editCustomer['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($editCustomer['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:10px;">
            <button type="submit" class="btn btn-success" style="width:auto;">
                <?= $editCustomer ? 'Update Customer' : 'Add Customer' ?>
            </button>
            <a href="<?= BASE_URL ?>/customers/index.php" class="btn btn-warning" style="width:auto;">Back to List</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
