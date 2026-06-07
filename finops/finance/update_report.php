<?php
require_once __DIR__ . '/../config/db.php';
requireRole(['admin', 'finance_officer']);
$pageTitle = 'Update Financial Report';

$message = '';
$error = '';

// Handle report creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'] ?? '';
    $reportData = trim($_POST['report_data'] ?? '');
    $reportId = $_POST['report_id'] ?? '';

    if (empty($reportType)) {
        $error = 'Report type is required.';
    } elseif (empty($reportData)) {
        $error = 'Report content cannot be empty.';
    } else {
        if ($reportId) {
            // Update existing report
            $stmt = $pdo->prepare("UPDATE reports SET report_type = ?, data = ?, date_generated = NOW() WHERE id = ?");
            $stmt->execute([$reportType, $reportData, $reportId]);
            $message = 'Report updated successfully.';
        } else {
            // Create new report
            $stmt = $pdo->prepare("INSERT INTO reports (report_type, generated_by, data) VALUES (?, ?, ?)");
            $stmt->execute([$reportType, $_SESSION['user_id'], $reportData]);
            $message = 'Financial report created successfully.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM reports WHERE id = ?")->execute([$id]);
    $message = 'Report deleted.';
}

// Get existing reports
$reports = $pdo->query("SELECT r.*, u.name as author_name FROM reports r JOIN users u ON r.generated_by = u.id ORDER BY r.date_generated DESC")->fetchAll();

// Edit mode
$editReport = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editReport = $stmt->fetch();
}

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
        <strong>Update Financial Report</strong> — Create or modify financial reports with notes, observations, and summary data. 
        These reports are stored in the system and accessible to administrators and management for review.
    </p>
</div>

<div class="form-container" style="max-width:100%;margin-bottom:30px;">
    <h3><?= $editReport ? 'Edit Report' : 'Create New Financial Report' ?></h3>
    <form method="POST">
        <?php if ($editReport): ?>
            <input type="hidden" name="report_id" value="<?= $editReport['id'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Report Type <span style="color:var(--danger);">*</span></label>
                <select name="report_type" required>
                    <option value="">— Select Report Type —</option>
                    <option value="monthly_financial" <?= ($editReport['report_type'] ?? '') === 'monthly_financial' ? 'selected' : '' ?>>Monthly Financial Summary</option>
                    <option value="quarterly_review" <?= ($editReport['report_type'] ?? '') === 'quarterly_review' ? 'selected' : '' ?>>Quarterly Review</option>
                    <option value="loan_performance" <?= ($editReport['report_type'] ?? '') === 'loan_performance' ? 'selected' : '' ?>>Loan Performance Report</option>
                    <option value="income_analysis" <?= ($editReport['report_type'] ?? '') === 'income_analysis' ? 'selected' : '' ?>>Income Analysis</option>
                    <option value="expense_analysis" <?= ($editReport['report_type'] ?? '') === 'expense_analysis' ? 'selected' : '' ?>>Expense Analysis</option>
                    <option value="risk_summary" <?= ($editReport['report_type'] ?? '') === 'risk_summary' ? 'selected' : '' ?>>Risk Summary Report</option>
                    <option value="custom" <?= ($editReport['report_type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Report</option>
                </select>
            </div>
            <div class="form-group">
                <label>Author</label>
                <input type="text" value="<?= sanitize($_SESSION['name']) ?>" disabled style="background:var(--light-gray);">
            </div>
        </div>
        <div class="form-group">
            <label>Report Content / Observations <span style="color:var(--danger);">*</span></label>
            <textarea name="report_data" rows="10" required placeholder="Enter your financial report observations, analysis notes, and findings here...

Example:
- Total deposits this month: Rwf 5,000,000
- Total withdrawals: Rwf 3,200,000
- Net position is positive
- 3 high-risk alerts flagged by AI
- Loan repayment rate: 87%
- Recommendations: ..."><?= sanitize($editReport['data'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-success" style="width:auto;">
                <?= $editReport ? '✏️ Update Report' : '📄 Save Report' ?>
            </button>
            <?php if ($editReport): ?>
                <a href="<?= BASE_URL ?>/finance/update_report.php" class="btn btn-warning" style="width:auto;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Existing Reports -->
<div class="table-container">
    <div class="table-header">
        <h3>Saved Financial Reports (<?= count($reports) ?>)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Author</th>
                <th>Preview</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= formatDateTime($report['date_generated']) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $report['report_type'])) ?></td>
                <td><?= sanitize($report['author_name']) ?></td>
                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--gray);font-size:0.85rem;">
                    <?= sanitize(substr($report['data'], 0, 100)) ?>...
                </td>
                <td>
                    <a href="?edit=<?= $report['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="<?= BASE_URL ?>/reports/view_report.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-primary">View</a>
                    <a href="?delete=<?= $report['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this report?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($reports)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--gray);">No reports created yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
