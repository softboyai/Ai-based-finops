<?php
require_once __DIR__ . '/../config/db.php';
requireRole(['admin', 'finance_officer']);
$pageTitle = 'Customer Management';

$message = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if customer has transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE customer_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $message = '<div class="alert alert-danger">Cannot delete customer with existing transactions.</div>';
    } else {
        $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
        $message = '<div class="alert alert-success">Customer deleted successfully.</div>';
    }
}

// Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE name LIKE ? OR account_number LIKE ? ORDER BY name");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
}
$customers = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<?= $message ?>

<div class="table-container">
    <div class="table-header">
        <h3>All Customers (<?= count($customers) ?>)</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <form method="GET" style="display:flex;gap:5px;">
                <input type="text" name="search" placeholder="Search name or account..." value="<?= sanitize($search) ?>" style="padding:8px 12px;border:1px solid var(--border);border-radius:4px;">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
            </form>
            <a href="<?= BASE_URL ?>/customers/add.php" class="btn btn-sm btn-success">+ Add Customer</a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Account #</th>
                <th>Name</th>
                <th>Type</th>
                <th>Balance</th>
                <th>Date Opened</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= sanitize($customer['account_number']) ?></td>
                <td><?= sanitize($customer['name']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $customer['account_type'])) ?></td>
                <td><?= formatCurrency($customer['balance']) ?></td>
                <td><?= formatDate($customer['date_opened']) ?></td>
                <td><span class="badge badge-<?= $customer['status'] === 'active' ? 'active' : 'inactive' ?>"><?= ucfirst($customer['status']) ?></span></td>
                <td>
                    <a href="<?= BASE_URL ?>/customers/view.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-info">View</a>
                    <a href="<?= BASE_URL ?>/customers/add.php?edit=<?= $customer['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?= $customer['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($customers)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--gray);">No customers found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
