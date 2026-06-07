<?php
require_once __DIR__ . '/../config/db.php';
requireRole('admin');
$pageTitle = 'System Settings';

$message = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['institution_name', 'founded_year', 'authorization', 'location', 'currency', 'contact_email', 'contact_phone', 'tagline'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $val = trim($_POST[$field]);
            $stmt->execute([$field, $val, $val]);
        }
    }
    $message = 'Settings updated successfully.';
}

// Load settings
$settingsRaw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$settings = [];
foreach ($settingsRaw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

// System stats
$dbSize = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size 
                       FROM information_schema.tables WHERE table_schema = 'goshen_finops'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalTransactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totalAlerts = $pdo->query("SELECT COUNT(*) FROM risk_alerts")->fetchColumn();
$loanAccounts = $pdo->query("SELECT COUNT(*) FROM customers WHERE account_type = 'loan'")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= sanitize($message) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Database Size</h3>
        <div class="value"><?= $dbSize ?> MB</div>
    </div>
    <div class="stat-card success">
        <h3>Total Users</h3>
        <div class="value"><?= $totalUsers ?></div>
    </div>
    <div class="stat-card info">
        <h3>Total Customers</h3>
        <div class="value"><?= $totalCustomers ?></div>
    </div>
    <div class="stat-card warning">
        <h3>Loan Accounts</h3>
        <div class="value"><?= $loanAccounts ?></div>
    </div>
</div>

<div class="form-container" style="max-width:100%;margin-bottom:30px;">
    <h3>Institution Settings — Goshen Finance Plc</h3>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Institution Name</label>
                <input type="text" name="institution_name" value="<?= sanitize($settings['institution_name'] ?? 'Goshen Finance Plc') ?>">
            </div>
            <div class="form-group">
                <label>Year Founded</label>
                <input type="text" name="founded_year" value="<?= sanitize($settings['founded_year'] ?? '2005') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Authorization Body</label>
                <input type="text" name="authorization" value="<?= sanitize($settings['authorization'] ?? 'MINICOM') ?>">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= sanitize($settings['location'] ?? 'Kigali, Rwanda') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Currency Symbol</label>
                <input type="text" name="currency" value="<?= sanitize($settings['currency'] ?? 'Rwf') ?>">
            </div>
            <div class="form-group">
                <label>Tagline</label>
                <input type="text" name="tagline" value="<?= sanitize($settings['tagline'] ?? 'AI-Based FinOps Management Information System') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Contact Email</label>
                <input type="email" name="contact_email" value="<?= sanitize($settings['contact_email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Contact Phone</label>
                <input type="text" name="contact_phone" value="<?= sanitize($settings['contact_phone'] ?? '') ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:auto;">Save Settings</button>
    </form>
</div>

<div class="form-container" style="max-width:100%;">
    <h3>System Information</h3>
    <table>
        <tbody>
            <tr><td><strong>Application</strong></td><td>Goshen Finance Plc — AI-Based FinOps MIS</td></tr>
            <tr><td><strong>Version</strong></td><td>1.0.0</td></tr>
            <tr><td><strong>Institution</strong></td><td><?= sanitize($settings['institution_name'] ?? 'Goshen Finance Plc') ?></td></tr>
            <tr><td><strong>Founded</strong></td><td><?= sanitize($settings['founded_year'] ?? '2005') ?></td></tr>
            <tr><td><strong>Authorized By</strong></td><td><?= sanitize($settings['authorization'] ?? 'MINICOM') ?></td></tr>
            <tr><td><strong>Location</strong></td><td><?= sanitize($settings['location'] ?? 'Kigali, Rwanda') ?></td></tr>
            <tr><td><strong>Currency</strong></td><td><?= sanitize($settings['currency'] ?? 'Rwf') ?> (Rwandan Francs)</td></tr>
            <tr><td><strong>Date Format</strong></td><td>DD/MM/YYYY (Rwanda standard)</td></tr>
            <tr><td><strong>PHP Version</strong></td><td><?= phpversion() ?></td></tr>
            <tr><td><strong>Server</strong></td><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td></tr>
            <tr><td><strong>Database</strong></td><td>MySQL (<?= $pdo->query("SELECT VERSION()")->fetchColumn() ?>)</td></tr>
            <tr><td><strong>Total Transactions</strong></td><td><?= number_format($totalTransactions) ?></td></tr>
            <tr><td><strong>Total Risk Alerts</strong></td><td><?= number_format($totalAlerts) ?></td></tr>
            <tr><td><strong>AI Engine</strong></td><td>PHP-based Risk Detection & Financial Insights v1.0</td></tr>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
