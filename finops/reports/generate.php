<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireRole(['admin', 'finance_officer']);
$pageTitle = 'Generate Report';

$message = '';
$generatedReport = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'] ?? '';
    $periodFrom = $_POST['period_from'] ?? '';
    $periodTo = $_POST['period_to'] ?? '';

    if (empty($reportType) || empty($periodFrom) || empty($periodTo)) {
        $message = '<div class="alert alert-danger">Please select report type and period.</div>';
    } else {
        // Generate report data based on type
        $reportContent = '';

        switch ($reportType) {
            case 'transaction_summary':
                $stmt = $pdo->prepare("SELECT type, COUNT(*) as count, SUM(amount) as total 
                                       FROM transactions WHERE DATE(date) >= ? AND DATE(date) <= ? GROUP BY type");
                $stmt->execute([$periodFrom, $periodTo]);
                $data = $stmt->fetchAll();
                $reportContent = "TRANSACTION SUMMARY REPORT\n";
                $reportContent .= "Period: " . formatDate($periodFrom) . " to " . formatDate($periodTo) . "\n\n";
                $total = 0;
                foreach ($data as $row) {
                    $reportContent .= ucfirst(str_replace('_', ' ', $row['type'])) . ": " . $row['count'] . " transactions, Total: " . formatCurrency($row['total']) . "\n";
                    $total += $row['total'];
                }
                $reportContent .= "\nGRAND TOTAL: " . formatCurrency($total);
                break;

            case 'income_expense':
                $stmt = $pdo->prepare("SELECT 
                    COALESCE(SUM(CASE WHEN type IN ('deposit','loan_repayment') THEN amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as expense
                    FROM transactions WHERE DATE(date) >= ? AND DATE(date) <= ?");
                $stmt->execute([$periodFrom, $periodTo]);
                $data = $stmt->fetch();
                $reportContent = "INCOME VS EXPENSE REPORT\n";
                $reportContent .= "Period: " . formatDate($periodFrom) . " to " . formatDate($periodTo) . "\n\n";
                $reportContent .= "Total Income (Deposits + Repayments): " . formatCurrency($data['income']) . "\n";
                $reportContent .= "Total Expense (Withdrawals): " . formatCurrency($data['expense']) . "\n";
                $reportContent .= "Net Position: " . formatCurrency($data['income'] - $data['expense']) . "\n";
                $reportContent .= "Status: " . ($data['income'] >= $data['expense'] ? 'POSITIVE' : 'NEGATIVE');
                break;

            case 'loan_status':
                $data = $pdo->query("SELECT c.name, c.account_number, 
                    (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'paid') as paid,
                    (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'missed') as missed,
                    (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'pending') as pending
                    FROM customers c WHERE c.account_type = 'loan' AND c.status = 'active'")->fetchAll();
                $reportContent = "LOAN STATUS REPORT\n";
                $reportContent .= "Generated: " . formatDateTime(date('Y-m-d H:i:s')) . "\n\n";
                foreach ($data as $row) {
                    $reportContent .= $row['name'] . " (" . $row['account_number'] . "): Paid=" . $row['paid'] . ", Missed=" . $row['missed'] . ", Pending=" . $row['pending'] . "\n";
                }
                if (empty($data)) $reportContent .= "No active loan accounts.";
                break;

            case 'customer_balances':
                $data = $pdo->query("SELECT name, account_number, account_type, balance FROM customers WHERE status = 'active' ORDER BY balance DESC")->fetchAll();
                $reportContent = "CUSTOMER BALANCES REPORT\n";
                $reportContent .= "Generated: " . formatDateTime(date('Y-m-d H:i:s')) . "\n\n";
                $totalBal = 0;
                foreach ($data as $row) {
                    $reportContent .= $row['name'] . " (" . $row['account_number'] . ") [" . ucfirst($row['account_type']) . "]: " . formatCurrency($row['balance']) . "\n";
                    $totalBal += $row['balance'];
                }
                $reportContent .= "\nTOTAL PORTFOLIO: " . formatCurrency($totalBal);
                break;

            case 'risk_report':
                $stmt = $pdo->prepare("SELECT ra.flag_reason, ra.risk_score, c.name, c.account_number, ra.flagged_at 
                                       FROM risk_alerts ra JOIN customers c ON ra.customer_id = c.id 
                                       WHERE DATE(ra.flagged_at) >= ? AND DATE(ra.flagged_at) <= ?
                                       ORDER BY ra.flagged_at DESC");
                $stmt->execute([$periodFrom, $periodTo]);
                $data = $stmt->fetchAll();
                $reportContent = "AI RISK ALERTS REPORT\n";
                $reportContent .= "Period: " . formatDate($periodFrom) . " to " . formatDate($periodTo) . "\n";
                $reportContent .= "Total Alerts: " . count($data) . "\n\n";
                foreach ($data as $row) {
                    $reportContent .= "[" . $row['risk_score'] . "] " . $row['name'] . " (" . $row['account_number'] . "): " . $row['flag_reason'] . " — " . formatDateTime($row['flagged_at']) . "\n";
                }
                if (empty($data)) $reportContent .= "No risk alerts in this period.";
                break;
        }

        // Save to database
        $stmt = $pdo->prepare("INSERT INTO reports (report_type, generated_by, data) VALUES (?, ?, ?)");
        $stmt->execute([$reportType, $_SESSION['user_id'], $reportContent]);
        $generatedReport = $reportContent;
        $message = '<div class="alert alert-success">Report generated and saved successfully!</div>';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?= $message ?>

<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:20px;padding:15px 20px;">
    <p style="color:var(--gray);font-size:0.9rem;">
        <strong>Generate Report</strong> — Select a report type and date range. The system will automatically compile the data 
        and save the report for future reference. Reports can be viewed, printed, or downloaded as PDF.
    </p>
</div>

<div class="form-container" style="max-width:100%;margin-bottom:30px;">
    <h3>Generate New Report</h3>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Report Type <span style="color:var(--danger);">*</span></label>
                <select name="report_type" required>
                    <option value="">— Select Report Type —</option>
                    <option value="transaction_summary">Transaction Summary</option>
                    <option value="income_expense">Income vs Expense</option>
                    <option value="loan_status">Loan Status Report</option>
                    <option value="customer_balances">Customer Balances</option>
                    <option value="risk_report">AI Risk Alerts Report</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Period From <span style="color:var(--danger);">*</span></label>
                <input type="date" name="period_from" required value="<?= date('Y-m-01') ?>">
            </div>
            <div class="form-group">
                <label>Period To <span style="color:var(--danger);">*</span></label>
                <input type="date" name="period_to" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-success" style="width:auto;">📄 Generate Report</button>
    </form>
</div>

<?php if ($generatedReport): ?>
<div class="chart-container" style="border-left:4px solid var(--success);">
    <h3 style="margin-bottom:10px;">✅ Generated Report</h3>
    <pre style="background:var(--light-gray);padding:20px;border-radius:6px;white-space:pre-wrap;font-family:'Courier New',monospace;font-size:0.9rem;line-height:1.6;border:1px solid var(--border);"><?= sanitize($generatedReport) ?></pre>
    <div style="margin-top:15px;">
        <button onclick="window.print()" class="btn btn-sm btn-warning">🖨️ Print</button>
        <a href="<?= BASE_URL ?>/finance/update_report.php" class="btn btn-sm btn-info">View All Reports</a>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
