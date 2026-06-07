<?php
/**
 * AI Risk Detection Engine
 * Goshen Finance Plc - FinOps MIS
 * 
 * This engine analyzes transactions for potential risks:
 * 1. Amount > 3x customer's average transaction
 * 2. 3+ transactions from same account within 1 hour
 * 3. Account balance drops below 10% of opening balance
 */

require_once __DIR__ . '/../config/db.php';

function analyzeTransaction($pdo, $transactionId) {
    // Get transaction details
    $stmt = $pdo->prepare("SELECT t.*, c.balance, c.opening_balance, c.account_number 
                           FROM transactions t 
                           JOIN customers c ON t.customer_id = c.id 
                           WHERE t.id = ?");
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();

    if (!$transaction) return;

    $flags = [];
    $customerId = $transaction['customer_id'];
    $amount = $transaction['amount'];

    // Rule 1: Amount > 3x average transaction
    $stmt = $pdo->prepare("SELECT AVG(amount) as avg_amount FROM transactions WHERE customer_id = ? AND id != ?");
    $stmt->execute([$customerId, $transactionId]);
    $avg = $stmt->fetch();

    if ($avg['avg_amount'] > 0 && $amount > (3 * $avg['avg_amount'])) {
        $flags[] = [
            'reason' => 'Transaction amount (Rwf ' . number_format($amount, 0) . ') exceeds 3x average (Rwf ' . number_format($avg['avg_amount'], 0) . ')',
            'severity' => 'High'
        ];
    }

    // Rule 2: 3+ transactions within 1 hour
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM transactions 
                           WHERE customer_id = ? AND date >= DATE_SUB(?, INTERVAL 1 HOUR) AND date <= ?");
    $stmt->execute([$customerId, $transaction['date'], $transaction['date']]);
    $frequency = $stmt->fetch();

    if ($frequency['count'] >= 3) {
        $flags[] = [
            'reason' => 'High frequency: ' . $frequency['count'] . ' transactions within 1 hour',
            'severity' => 'Medium'
        ];
    }

    // Rule 3: Balance below 10% of opening balance
    if ($transaction['opening_balance'] > 0 && $transaction['balance'] < (0.10 * $transaction['opening_balance'])) {
        $flags[] = [
            'reason' => 'Account balance (Rwf ' . number_format($transaction['balance'], 0) . ') below 10% of opening balance (Rwf ' . number_format($transaction['opening_balance'], 0) . ')',
            'severity' => 'High'
        ];
    }

    // Store flags in risk_alerts table
    foreach ($flags as $flag) {
        $stmt = $pdo->prepare("INSERT INTO risk_alerts (transaction_id, customer_id, flag_reason, risk_score, flagged_at) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$transactionId, $customerId, $flag['reason'], $flag['severity']]);
    }

    return $flags;
}

function calculateOverallRiskScore($pdo, $customerId) {
    $stmt = $pdo->prepare("SELECT risk_score, COUNT(*) as count FROM risk_alerts 
                           WHERE customer_id = ? AND reviewed = 0 
                           GROUP BY risk_score");
    $stmt->execute([$customerId]);
    $scores = $stmt->fetchAll();

    $totalScore = 0;
    foreach ($scores as $score) {
        switch ($score['risk_score']) {
            case 'High': $totalScore += $score['count'] * 3; break;
            case 'Medium': $totalScore += $score['count'] * 2; break;
            case 'Low': $totalScore += $score['count'] * 1; break;
        }
    }

    return $totalScore;
}

function getTopRiskAccounts($pdo, $limit = 5) {
    $limit = (int)$limit;
    $stmt = $pdo->query("SELECT c.id, c.name, c.account_number,
                           SUM(CASE WHEN ra.risk_score = 'High' THEN 3 
                                    WHEN ra.risk_score = 'Medium' THEN 2 
                                    ELSE 1 END) as total_risk_score,
                           COUNT(ra.id) as alert_count
                           FROM risk_alerts ra
                           JOIN customers c ON ra.customer_id = c.id
                           WHERE ra.reviewed = 0
                           GROUP BY c.id, c.name, c.account_number
                           ORDER BY total_risk_score DESC
                           LIMIT $limit");
    return $stmt->fetchAll();
}

function getMonthlyTrend($pdo) {
    $currentMonth = date('Y-m-01');
    $lastMonth = date('Y-m-01', strtotime('-1 month'));

    // Current month total
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE date >= ?");
    $stmt->execute([$currentMonth]);
    $current = $stmt->fetch()['total'];

    // Last month total
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE date >= ? AND date < ?");
    $stmt->execute([$lastMonth, $currentMonth]);
    $last = $stmt->fetch()['total'];

    $change = $last > 0 ? (($current - $last) / $last) * 100 : 0;

    return [
        'current_month' => $current,
        'last_month' => $last,
        'change_percent' => round($change, 2)
    ];
}

function getLoanDefaultRisk($pdo) {
    $stmt = $pdo->prepare("SELECT c.id, c.name, c.account_number, COUNT(lr.id) as missed_payments
                           FROM loan_repayments lr
                           JOIN customers c ON lr.customer_id = c.id
                           WHERE lr.status = 'missed'
                           GROUP BY c.id, c.name, c.account_number
                           HAVING missed_payments >= 2
                           ORDER BY missed_payments DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMonthlyTransactionData($pdo, $months = 6) {
    $data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $monthStart = date('Y-m-01', strtotime("-$i months"));
        $monthEnd = date('Y-m-t', strtotime("-$i months"));
        $monthLabel = date('M Y', strtotime("-$i months"));

        $stmt = $pdo->prepare("SELECT 
                               COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as deposits,
                               COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as withdrawals,
                               COALESCE(SUM(CASE WHEN type = 'loan_repayment' THEN amount ELSE 0 END), 0) as repayments
                               FROM transactions WHERE date >= ? AND date <= ?");
        $stmt->execute([$monthStart, $monthEnd . ' 23:59:59']);
        $row = $stmt->fetch();

        $data[] = [
            'month' => $monthLabel,
            'deposits' => (float)$row['deposits'],
            'withdrawals' => (float)$row['withdrawals'],
            'repayments' => (float)$row['repayments']
        ];
    }
    return $data;
}
