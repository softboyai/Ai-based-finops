<?php
/**
 * Goshen Finance Plc — Data Seeder
 * Run this ONCE after install.php to populate sample data
 * URL: http://localhost/AI-Based%20FinOps/finops/seed.php
 * 
 * This creates:
 * - 3 users (admin, finance officer, management)
 * - 10 customers (various account types)
 * - 50+ transactions (deposits, withdrawals, repayments)
 * - Loan repayment schedules (with some missed)
 * - AI Risk alerts (auto-generated from transactions)
 * 
 * DELETE THIS FILE after seeding.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/ai/risk_engine.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ============ USERS ============
        $users = [
            ['Jean Baptiste Uwimana', 'jbaptiste@goshenfinance.rw', 'jbaptiste', password_hash('officer123', PASSWORD_DEFAULT), 'finance_officer'],
            ['Marie Claire Mukamana', 'mcmukamana@goshenfinance.rw', 'mcmukamana', password_hash('officer123', PASSWORD_DEFAULT), 'finance_officer'],
            ['Patrick Habimana', 'phabimana@goshenfinance.rw', 'phabimana', password_hash('manage123', PASSWORD_DEFAULT), 'management'],
        ];
        
        foreach ($users as $u) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, username, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute($u);
        }

        // Get officer IDs for processed_by
        $officerId1 = $pdo->query("SELECT id FROM users WHERE username = 'jbaptiste'")->fetchColumn();
        $officerId2 = $pdo->query("SELECT id FROM users WHERE username = 'mcmukamana'")->fetchColumn();
        $adminId = $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetchColumn();

        if (!$officerId1) $officerId1 = $adminId;
        if (!$officerId2) $officerId2 = $adminId;

        // ============ CUSTOMERS ============
        $customers = [
            ['Mugabo Emmanuel', 'GF-SAV-001', 'savings', 1500000, '2020-03-15'],
            ['Uwase Diane', 'GF-SAV-002', 'savings', 850000, '2021-06-20'],
            ['Ndayisaba Jean Pierre', 'GF-CUR-001', 'current', 3200000, '2019-01-10'],
            ['Mutoni Grace', 'GF-CUR-002', 'current', 2100000, '2022-02-14'],
            ['Habimana Eric', 'GF-LON-001', 'loan', 5000000, '2023-01-05'],
            ['Uwimana Alice', 'GF-LON-002', 'loan', 3000000, '2023-04-12'],
            ['Niyonzima David', 'GF-LON-003', 'loan', 8000000, '2022-11-01'],
            ['Mukeshimana Vestine', 'GF-INV-001', 'investment', 10000000, '2020-08-25'],
            ['Bizimana Patrick', 'GF-INV-002', 'investment', 5500000, '2021-12-03'],
            ['Ingabire Jeanne', 'GF-SAV-003', 'savings', 450000, '2024-01-15'],
        ];

        foreach ($customers as $c) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO customers (name, account_number, account_type, balance, opening_balance, date_opened, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[3], $c[4]]);
        }

        // Get customer IDs
        $customerIds = [];
        $allCustomers = $pdo->query("SELECT id, account_number, balance FROM customers")->fetchAll();
        foreach ($allCustomers as $ac) {
            $customerIds[$ac['account_number']] = $ac['id'];
        }

        // ============ TRANSACTIONS ============
        // Generate transactions over the last 3 months
        $transactions = [
            // Normal deposits
            [$customerIds['GF-SAV-001'] ?? 1, 'deposit', 200000, '-60 days', $officerId1, 'Monthly salary deposit'],
            [$customerIds['GF-SAV-001'] ?? 1, 'deposit', 180000, '-30 days', $officerId1, 'Monthly salary deposit'],
            [$customerIds['GF-SAV-001'] ?? 1, 'deposit', 200000, '-5 days', $officerId2, 'Monthly salary deposit'],
            [$customerIds['GF-SAV-002'] ?? 2, 'deposit', 150000, '-45 days', $officerId1, 'Business income'],
            [$customerIds['GF-SAV-002'] ?? 2, 'deposit', 120000, '-20 days', $officerId2, 'Business income'],
            [$customerIds['GF-CUR-001'] ?? 3, 'deposit', 500000, '-50 days', $officerId1, 'Contract payment'],
            [$customerIds['GF-CUR-001'] ?? 3, 'deposit', 800000, '-25 days', $officerId1, 'Major contract payment'],
            [$customerIds['GF-CUR-002'] ?? 4, 'deposit', 350000, '-40 days', $officerId2, 'Sales revenue'],
            [$customerIds['GF-INV-001'] ?? 8, 'deposit', 1000000, '-35 days', $officerId1, 'Investment top-up'],
            [$customerIds['GF-INV-002'] ?? 9, 'deposit', 500000, '-28 days', $officerId2, 'Quarterly investment'],

            // Normal withdrawals
            [$customerIds['GF-SAV-001'] ?? 1, 'withdrawal', 50000, '-55 days', $officerId1, 'ATM withdrawal'],
            [$customerIds['GF-SAV-001'] ?? 1, 'withdrawal', 80000, '-22 days', $officerId2, 'Rent payment'],
            [$customerIds['GF-SAV-002'] ?? 2, 'withdrawal', 40000, '-38 days', $officerId1, 'Utility bills'],
            [$customerIds['GF-CUR-001'] ?? 3, 'withdrawal', 200000, '-42 days', $officerId1, 'Supplier payment'],
            [$customerIds['GF-CUR-001'] ?? 3, 'withdrawal', 150000, '-15 days', $officerId2, 'Staff salaries'],
            [$customerIds['GF-CUR-002'] ?? 4, 'withdrawal', 100000, '-30 days', $officerId1, 'Operating expenses'],

            // Loan repayments
            [$customerIds['GF-LON-001'] ?? 5, 'loan_repayment', 250000, '-58 days', $officerId1, 'Monthly loan installment'],
            [$customerIds['GF-LON-001'] ?? 5, 'loan_repayment', 250000, '-28 days', $officerId1, 'Monthly loan installment'],
            [$customerIds['GF-LON-002'] ?? 6, 'loan_repayment', 150000, '-50 days', $officerId2, 'Loan payment'],
            [$customerIds['GF-LON-002'] ?? 6, 'loan_repayment', 150000, '-20 days', $officerId2, 'Loan payment'],
            [$customerIds['GF-LON-003'] ?? 7, 'loan_repayment', 400000, '-45 days', $officerId1, 'Business loan installment'],

            // === SUSPICIOUS TRANSACTIONS (will trigger AI alerts) ===
            
            // Large transaction: 4x the average for this customer (triggers Rule 1: >3x average)
            [$customerIds['GF-SAV-001'] ?? 1, 'deposit', 900000, '-2 days', $officerId1, 'Unusual large deposit'],
            
            // Multiple transactions within 1 hour (triggers Rule 2: frequency)
            [$customerIds['GF-CUR-001'] ?? 3, 'withdrawal', 100000, '-1 day', $officerId2, 'Transfer 1'],
            [$customerIds['GF-CUR-001'] ?? 3, 'withdrawal', 150000, '-1 day', $officerId2, 'Transfer 2'],
            [$customerIds['GF-CUR-001'] ?? 3, 'withdrawal', 200000, '-1 day', $officerId1, 'Transfer 3'],
            
            // Large withdrawal bringing balance low (triggers Rule 3: balance < 10% of opening)
            [$customerIds['GF-SAV-003'] ?? 10, 'withdrawal', 420000, '-3 days', $officerId1, 'Large withdrawal - emergency'],

            // Another anomaly: huge deposit for small account
            [$customerIds['GF-SAV-003'] ?? 10, 'deposit', 2000000, '-1 day', $officerId2, 'Unexplained large deposit'],

            // More normal activity for volume
            [$customerIds['GF-SAV-002'] ?? 2, 'deposit', 200000, '-10 days', $officerId1, 'Client payment received'],
            [$customerIds['GF-CUR-002'] ?? 4, 'deposit', 450000, '-8 days', $officerId2, 'Invoice payment'],
            [$customerIds['GF-CUR-002'] ?? 4, 'withdrawal', 180000, '-6 days', $officerId1, 'Payroll'],
            [$customerIds['GF-INV-001'] ?? 8, 'deposit', 750000, '-4 days', $officerId1, 'Monthly auto-invest'],
        ];

        $transactionIds = [];
        foreach ($transactions as $t) {
            $transDate = date('Y-m-d H:i:s', strtotime($t[3]));
            $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, type, amount, date, processed_by, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$t[0], $t[1], $t[2], $transDate, $t[4], $t[5]]);
            $txId = $pdo->lastInsertId();
            $transactionIds[] = $txId;

            // Update balances
            if ($t[1] === 'deposit' || $t[1] === 'loan_repayment') {
                $pdo->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?")->execute([$t[2], $t[0]]);
            } elseif ($t[1] === 'withdrawal') {
                $pdo->prepare("UPDATE customers SET balance = balance - ? WHERE id = ?")->execute([$t[2], $t[0]]);
            }
        }

        // ============ RUN AI RISK ENGINE ON ALL TRANSACTIONS ============
        $alertCount = 0;
        foreach ($transactionIds as $txId) {
            $flags = analyzeTransaction($pdo, $txId);
            if (!empty($flags)) $alertCount += count($flags);
        }

        // ============ LOAN REPAYMENT SCHEDULES ============
        $loanCustomer1 = $customerIds['GF-LON-001'] ?? 5;
        $loanCustomer2 = $customerIds['GF-LON-002'] ?? 6;
        $loanCustomer3 = $customerIds['GF-LON-003'] ?? 7;

        $loanSchedules = [
            // Customer 1: mostly paid
            [$loanCustomer1, '-90 days', 250000, 250000, 'paid'],
            [$loanCustomer1, '-60 days', 250000, 250000, 'paid'],
            [$loanCustomer1, '-30 days', 250000, 250000, 'paid'],
            [$loanCustomer1, '+1 day', 250000, 0, 'pending'],
            [$loanCustomer1, '+30 days', 250000, 0, 'pending'],

            // Customer 2: some missed (will trigger AI loan default risk)
            [$loanCustomer2, '-90 days', 150000, 150000, 'paid'],
            [$loanCustomer2, '-60 days', 150000, 0, 'missed'],
            [$loanCustomer2, '-30 days', 150000, 0, 'missed'],
            [$loanCustomer2, '+1 day', 150000, 0, 'pending'],

            // Customer 3: multiple missed (HIGH default risk)
            [$loanCustomer3, '-120 days', 400000, 400000, 'paid'],
            [$loanCustomer3, '-90 days', 400000, 0, 'missed'],
            [$loanCustomer3, '-60 days', 400000, 0, 'missed'],
            [$loanCustomer3, '-30 days', 400000, 0, 'missed'],
            [$loanCustomer3, '+1 day', 400000, 0, 'pending'],
        ];

        foreach ($loanSchedules as $ls) {
            $dueDate = date('Y-m-d', strtotime($ls[1]));
            $stmt = $pdo->prepare("INSERT INTO loan_repayments (customer_id, due_date, amount_due, amount_paid, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$ls[0], $dueDate, $ls[2], $ls[3], $ls[4]]);
        }

        // ============ SAMPLE REPORTS ============
        $reportContent1 = "MONTHLY FINANCIAL SUMMARY - " . date('F Y') . "\n\n";
        $reportContent1 .= "Total Deposits: Rwf 5,420,000\n";
        $reportContent1 .= "Total Withdrawals: Rwf 1,570,000\n";
        $reportContent1 .= "Loan Repayments Received: Rwf 1,200,000\n";
        $reportContent1 .= "Net Position: POSITIVE (Rwf 5,050,000)\n\n";
        $reportContent1 .= "Observations:\n";
        $reportContent1 .= "- Customer acquisition grew by 12% this month\n";
        $reportContent1 .= "- 3 AI risk alerts flagged for review\n";
        $reportContent1 .= "- Loan repayment rate: 71% (below target of 85%)\n";
        $reportContent1 .= "- Recommendation: Follow up on GF-LON-002 and GF-LON-003 missed payments\n";

        $stmt = $pdo->prepare("INSERT INTO reports (report_type, generated_by, data) VALUES (?, ?, ?)");
        $stmt->execute(['monthly_financial', $adminId ?: 1, $reportContent1]);

        $reportContent2 = "AI RISK ANALYSIS SUMMARY\n\n";
        $reportContent2 .= "Period: Last 30 days\n";
        $reportContent2 .= "Total Alerts Generated: $alertCount\n\n";
        $reportContent2 .= "Key Findings:\n";
        $reportContent2 .= "- Account GF-SAV-001: Large deposit anomaly detected (4.5x average)\n";
        $reportContent2 .= "- Account GF-CUR-001: High-frequency transactions (3 within 1 hour)\n";
        $reportContent2 .= "- Account GF-SAV-003: Balance dropped below 10% of opening balance\n";
        $reportContent2 .= "- Account GF-LON-003: 3 consecutive missed loan payments — HIGH DEFAULT RISK\n\n";
        $reportContent2 .= "Recommendations:\n";
        $reportContent2 .= "- Immediate review of GF-CUR-001 frequency pattern\n";
        $reportContent2 .= "- Schedule meeting with GF-LON-003 account holder\n";
        $reportContent2 .= "- Enhanced monitoring on GF-SAV-003\n";

        $stmt->execute(['risk_summary', $adminId ?: 1, $reportContent2]);

        $message = "✅ <strong>Seed data loaded successfully!</strong><br><br>
            <strong>Created:</strong><br>
            • 3 additional users (2 Finance Officers + 1 Management)<br>
            • 10 customers (Savings, Loan, Investment, Current)<br>
            • " . count($transactions) . " transactions (with AI analysis)<br>
            • $alertCount AI risk alerts generated<br>
            • " . count($loanSchedules) . " loan repayment records (including missed payments)<br>
            • 2 sample financial reports<br><br>
            <strong>AI Features now visible:</strong><br>
            • Risk Alerts page shows flagged transactions (anomalies, frequency, low balance)<br>
            • AI Insights shows trends, top risk accounts, loan default predictions<br>
            • Performance Monitor shows KPIs and officer activity<br><br>
            <strong>Test logins:</strong><br>
            • Finance Officer: <code>jbaptiste</code> / <code>officer123</code><br>
            • Management: <code>phabimana</code> / <code>manage123</code><br><br>
            <em>Please delete this file now for security.</em>";

    } catch (PDOException $e) {
        $error = "Seeding failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seed Data — Goshen Finance Plc</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-box" style="max-width:550px;">
        <img src="<?= BASE_URL ?>/assets/images/goshen.png" alt="Logo" style="display:block;width:70px;height:70px;border-radius:50%;object-fit:cover;margin:0 auto 15px;border:3px solid #003366;">
        <h1>Goshen Finance Plc</h1>
        <p class="subtitle">AI-Based FinOps MIS — Sample Data Seeder</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary">Go to Login</a>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <form method="POST"><button type="submit" class="btn btn-primary">Retry</button></form>
        <?php else: ?>
            <div style="margin-bottom:20px;color:var(--gray);font-size:0.88rem;line-height:1.7;">
                <p><strong>This will populate the system with sample data:</strong></p>
                <ul style="padding-left:20px;margin-top:8px;">
                    <li>10 customers (Savings, Loan, Investment, Current accounts)</li>
                    <li>30+ transactions including suspicious ones</li>
                    <li>Loan repayment schedules with missed payments</li>
                    <li>AI Risk Engine will auto-analyze all transactions</li>
                    <li>2 Finance Officers + 1 Management user</li>
                </ul>
                <p style="margin-top:12px;"><strong>After seeding, the AI features will show:</strong></p>
                <ul style="padding-left:20px;margin-top:8px;">
                    <li>🤖 Risk alerts (anomaly detection, frequency analysis)</li>
                    <li>📊 AI Insights with trends and charts</li>
                    <li>⚠️ Loan default predictions</li>
                    <li>📈 Performance monitoring KPIs</li>
                </ul>
            </div>
            <form method="POST">
                <button type="submit" class="btn btn-primary">🚀 Seed Sample Data</button>
            </form>
            <p style="text-align:center;margin-top:15px;font-size:0.75rem;color:var(--gray);">
                Make sure you've run install.php first. Delete this file after seeding.
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
