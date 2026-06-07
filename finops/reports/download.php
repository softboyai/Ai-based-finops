<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../ai/risk_engine.php';
requireLogin();

$reportType = $_GET['report'] ?? 'monthly_summary';
$month = $_GET['month'] ?? date('Y-m');

$monthStart = $month . '-01';
$monthEnd = date('Y-m-t', strtotime($monthStart));
$reportTitle = '';

// Gather data based on report type
switch ($reportType) {
    case 'monthly_summary':
        $reportTitle = 'Monthly Transaction Summary';
        $stmt = $pdo->prepare("SELECT type, COUNT(*) as count, SUM(amount) as total 
                               FROM transactions WHERE date >= ? AND date <= ? GROUP BY type");
        $stmt->execute([$monthStart, $monthEnd . ' 23:59:59']);
        $data = $stmt->fetchAll();
        break;

    case 'balance':
        $reportTitle = 'Account Balance Report';
        $data = $pdo->query("SELECT name, account_number, account_type, balance, opening_balance, status 
                             FROM customers WHERE status = 'active' ORDER BY balance DESC")->fetchAll();
        break;

    case 'income_expense':
        $reportTitle = 'Income vs Expense Report';
        $stmt = $pdo->prepare("SELECT 
            COALESCE(SUM(CASE WHEN type = 'deposit' OR type = 'loan_repayment' THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as expense
            FROM transactions WHERE date >= ? AND date <= ?");
        $stmt->execute([$monthStart, $monthEnd . ' 23:59:59']);
        $data = $stmt->fetch();
        break;

    case 'loans':
        $reportTitle = 'Loan Portfolio Report';
        $data = $pdo->query("SELECT c.name, c.account_number,
            (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'paid') as paid_count,
            (SELECT COUNT(*) FROM loan_repayments lr WHERE lr.customer_id = c.id AND lr.status = 'missed') as missed_count,
            (SELECT COALESCE(SUM(lr.amount_due), 0) FROM loan_repayments lr WHERE lr.customer_id = c.id) as total_due,
            (SELECT COALESCE(SUM(lr.amount_paid), 0) FROM loan_repayments lr WHERE lr.customer_id = c.id) as total_paid
            FROM customers c WHERE c.account_type = 'loan' AND c.status = 'active' ORDER BY c.name")->fetchAll();
        break;

    case 'risk_alerts':
        $reportTitle = 'AI Risk Alerts Report';
        $data = $pdo->query("SELECT ra.*, c.name as customer_name, c.account_number, t.amount, t.type as transaction_type
                             FROM risk_alerts ra JOIN customers c ON ra.customer_id = c.id 
                             JOIN transactions t ON ra.transaction_id = t.id
                             ORDER BY ra.flagged_at DESC LIMIT 50")->fetchAll();
        break;

    case 'ai_insights':
        $reportTitle = 'AI Financial Insights Report';
        $trend = getMonthlyTrend($pdo);
        $topRisk = getTopRiskAccounts($pdo, 5);
        $loanDefaults = getLoanDefaultRisk($pdo);
        break;

    default:
        $reportTitle = 'Financial Report';
        $data = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $reportTitle ?> — Goshen Finance Plc</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
            padding: 20px;
        }
        .report-header {
            text-align: center;
            border-bottom: 3px solid #003366;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .report-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .report-header h1 {
            color: #003366;
            font-size: 20pt;
            margin-bottom: 3px;
        }
        .report-header .subtitle {
            color: #666;
            font-size: 10pt;
        }
        .report-header .auth-line {
            color: #888;
            font-size: 9pt;
            margin-top: 5px;
        }
        .report-title {
            background: #003366;
            color: #fff;
            padding: 12px 20px;
            margin-bottom: 20px;
            font-size: 13pt;
            font-weight: 700;
        }
        .report-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 9pt;
            color: #666;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
            font-size: 10pt;
        }
        table th {
            background: #003366;
            color: #fff;
            font-weight: 600;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .summary-box {
            background: #f4f6f9;
            border: 1px solid #ddd;
            border-left: 4px solid #003366;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .summary-box h4 {
            color: #003366;
            margin-bottom: 8px;
        }
        .risk-high { color: #dc3545; font-weight: 700; }
        .risk-medium { color: #fd7e14; font-weight: 700; }
        .risk-low { color: #28a745; font-weight: 700; }
        .report-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #003366;
            text-align: center;
            font-size: 8pt;
            color: #888;
        }
        .report-footer strong {
            color: #003366;
        }
        .ai-badge {
            display: inline-block;
            background: #003366;
            color: #fff;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: 600;
        }
        .download-bar {
            background: #003366;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -20px -20px 20px -20px;
        }
        .download-bar button {
            background: #fff;
            color: #003366;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: 700;
            cursor: pointer;
            font-size: 10pt;
        }
        .download-bar button:hover {
            background: #f0f0f0;
        }
        .download-bar a {
            color: #fff;
            text-decoration: none;
            font-size: 9pt;
            opacity: 0.8;
        }
        @media print {
            .download-bar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<!-- Download Control Bar (hidden in print/PDF) -->
<div class="download-bar">
    <div>
        <strong>Goshen Finance Plc</strong> — <?= $reportTitle ?>
        <span class="ai-badge">AI-Generated</span>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="<?= BASE_URL ?>/reports/index.php">← Back to Reports</a>
        <button onclick="window.print()">📥 Download PDF</button>
    </div>
</div>

<!-- Report Header with Logo -->
<div class="report-header">
    <img src="<?= BASE_URL ?>/assets/images/goshen.png" alt="Goshen Finance Plc Logo">
    <h1>Goshen Finance Plc</h1>
    <p class="subtitle">AI-Based FinOps Management Information System</p>
    <p class="auth-line">Established 2005 | Authorized by MINICOM | Kigali, Rwanda</p>
</div>

<div class="report-title"><?= $reportTitle ?> — <?= date('F Y', strtotime($monthStart)) ?></div>

<div class="report-meta">
    <span>Generated: <?= formatDateTime(date('Y-m-d H:i:s')) ?></span>
    <span>Generated By: <?= sanitize($_SESSION['name']) ?> (<?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?>)</span>
    <span>Period: <?= formatDate($monthStart) ?> — <?= formatDate($monthEnd) ?></span>
</div>

<?php if ($reportType === 'monthly_summary'): ?>
<div class="summary-box">
    <h4>📊 Monthly Transaction Summary</h4>
    <p>Overview of all transactions processed by Goshen Finance Plc during <?= date('F Y', strtotime($monthStart)) ?>.</p>
</div>
<table>
    <thead>
        <tr>
            <th>Transaction Type</th>
            <th>Number of Transactions</th>
            <th>Total Amount (Rwf)</th>
        </tr>
    </thead>
    <tbody>
        <?php $grandTotal = 0; foreach ($data as $row): $grandTotal += $row['total']; ?>
        <tr>
            <td><?= ucfirst(str_replace('_', ' ', $row['type'])) ?></td>
            <td><?= number_format($row['count']) ?></td>
            <td><?= formatCurrency($row['total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="font-weight:bold;background:#e8f0fe;">
            <td>GRAND TOTAL</td>
            <td>—</td>
            <td><?= formatCurrency($grandTotal) ?></td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<?php if ($reportType === 'balance'): ?>
<div class="summary-box">
    <h4>💰 Account Balance Report</h4>
    <p>Current balances for all active customer accounts at Goshen Finance Plc.</p>
</div>
<table>
    <thead>
        <tr>
            <th>Account #</th>
            <th>Customer Name</th>
            <th>Account Type</th>
            <th>Opening Balance</th>
            <th>Current Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalBal = 0; foreach ($data as $row): $totalBal += $row['balance']; ?>
        <tr>
            <td><?= sanitize($row['account_number']) ?></td>
            <td><?= sanitize($row['name']) ?></td>
            <td><?= ucfirst($row['account_type']) ?></td>
            <td><?= formatCurrency($row['opening_balance']) ?></td>
            <td><?= formatCurrency($row['balance']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="font-weight:bold;background:#e8f0fe;">
            <td colspan="4">TOTAL PORTFOLIO VALUE</td>
            <td><?= formatCurrency($totalBal) ?></td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<?php if ($reportType === 'income_expense'): ?>
<div class="summary-box">
    <h4>📈 Income vs Expense Analysis</h4>
    <p>AI-analyzed financial position of Goshen Finance Plc for <?= date('F Y', strtotime($monthStart)) ?>.</p>
</div>
<table>
    <thead>
        <tr><th>Category</th><th>Amount (Rwf)</th><th>Notes</th></tr>
    </thead>
    <tbody>
        <tr><td>Income (Deposits + Loan Repayments)</td><td><?= formatCurrency($data['income']) ?></td><td>Inflows</td></tr>
        <tr><td>Expenses (Withdrawals)</td><td><?= formatCurrency($data['expense']) ?></td><td>Outflows</td></tr>
        <tr style="font-weight:bold;background:#e8f0fe;">
            <td>NET POSITION</td>
            <td><?= formatCurrency($data['income'] - $data['expense']) ?></td>
            <td><?= ($data['income'] - $data['expense']) >= 0 ? '✅ Positive' : '⚠️ Negative' ?></td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<?php if ($reportType === 'loans'): ?>
<div class="summary-box">
    <h4>🏦 Loan Portfolio Report</h4>
    <p>Status of all active loan accounts managed by Goshen Finance Plc. AI risk flags highlight accounts with missed payments.</p>
</div>
<table>
    <thead>
        <tr>
            <th>Customer</th>
            <th>Account #</th>
            <th>Total Due</th>
            <th>Total Paid</th>
            <th>Paid</th>
            <th>Missed</th>
            <th>AI Risk Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= sanitize($row['name']) ?></td>
            <td><?= sanitize($row['account_number']) ?></td>
            <td><?= formatCurrency($row['total_due']) ?></td>
            <td><?= formatCurrency($row['total_paid']) ?></td>
            <td><?= $row['paid_count'] ?></td>
            <td><?= $row['missed_count'] ?></td>
            <td>
                <?php if ($row['missed_count'] >= 2): ?>
                    <span class="risk-high">⚠ HIGH — Default Risk</span>
                <?php elseif ($row['missed_count'] >= 1): ?>
                    <span class="risk-medium">⚡ MEDIUM — Watch</span>
                <?php else: ?>
                    <span class="risk-low">✅ LOW — Good Standing</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if ($reportType === 'risk_alerts'): ?>
<div class="summary-box">
    <h4>🤖 AI Risk Alerts Report</h4>
    <p>Transactions flagged by the Goshen Finance AI Risk Engine. The system automatically detects anomalous patterns including unusual amounts, high-frequency transactions, and low balance warnings.</p>
</div>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Transaction</th>
            <th>Amount</th>
            <th>AI Flag Reason</th>
            <th>Risk Score</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $alert): ?>
        <tr>
            <td><?= formatDateTime($alert['flagged_at']) ?></td>
            <td><?= sanitize($alert['customer_name']) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $alert['transaction_type'])) ?></td>
            <td><?= formatCurrency($alert['amount']) ?></td>
            <td><?= sanitize($alert['flag_reason']) ?></td>
            <td><span class="risk-<?= strtolower($alert['risk_score']) ?>"><?= $alert['risk_score'] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if ($reportType === 'ai_insights'): ?>
<div class="summary-box">
    <h4>🤖 AI-Powered Financial Insights</h4>
    <p>Intelligent analysis generated by the Goshen Finance AI Engine based on transaction patterns, risk accumulation, and loan performance data.</p>
</div>

<h3 style="color:#003366;margin:15px 0 10px;">Monthly Trend Analysis</h3>
<table>
    <tr><td><strong>Current Month Volume</strong></td><td><?= formatCurrency($trend['current_month']) ?></td></tr>
    <tr><td><strong>Last Month Volume</strong></td><td><?= formatCurrency($trend['last_month']) ?></td></tr>
    <tr><td><strong>Change</strong></td><td><span class="<?= $trend['change_percent'] >= 0 ? 'risk-low' : 'risk-high' ?>"><?= $trend['change_percent'] >= 0 ? '+' : '' ?><?= $trend['change_percent'] ?>%</span></td></tr>
</table>

<h3 style="color:#003366;margin:15px 0 10px;">Top 5 High-Risk Accounts (AI Scored)</h3>
<table>
    <thead><tr><th>Rank</th><th>Customer</th><th>Account #</th><th>AI Risk Score</th><th>Alert Count</th></tr></thead>
    <tbody>
        <?php foreach ($topRisk as $i => $acc): ?>
        <tr>
            <td>#<?= $i+1 ?></td>
            <td><?= sanitize($acc['name']) ?></td>
            <td><?= sanitize($acc['account_number']) ?></td>
            <td><span class="<?= $acc['total_risk_score'] >= 9 ? 'risk-high' : ($acc['total_risk_score'] >= 5 ? 'risk-medium' : 'risk-low') ?>"><?= $acc['total_risk_score'] ?></span></td>
            <td><?= $acc['alert_count'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($topRisk)): ?>
        <tr><td colspan="5" style="text-align:center;color:#888;">No risk data available</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($loanDefaults)): ?>
<h3 style="color:#003366;margin:15px 0 10px;">Loan Default Risk (AI Prediction)</h3>
<table>
    <thead><tr><th>Customer</th><th>Account #</th><th>Missed Payments</th><th>AI Assessment</th></tr></thead>
    <tbody>
        <?php foreach ($loanDefaults as $ld): ?>
        <tr>
            <td><?= sanitize($ld['name']) ?></td>
            <td><?= sanitize($ld['account_number']) ?></td>
            <td><?= $ld['missed_payments'] ?></td>
            <td><span class="risk-high">⚠ High Default Probability</span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php endif; ?>

<!-- Report Footer -->
<div class="report-footer">
    <p><strong>CONFIDENTIAL — Goshen Finance Plc</strong></p>
    <p>This report was generated by the AI-Based FinOps Management Information System.</p>
    <p>Authorized by MINICOM | Established 2005 | Kigali, Rwanda</p>
    <p style="margin-top:8px;">© <?= date('Y') ?> Goshen Finance Plc. Unauthorized distribution is prohibited.</p>
</div>

</body>
</html>
