<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();
$pageTitle = 'View Report';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT r.*, u.name as author_name FROM reports r JOIN users u ON r.generated_by = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$report = $stmt->fetch();

if (!$report) {
    header('Location: ' . BASE_URL . '/reports/index.php');
    exit;
}

$pageTitle = ucfirst(str_replace('_', ' ', $report['report_type'])) . ' Report';

include __DIR__ . '/../includes/header.php';
?>

<div class="chart-container" style="border-left:4px solid var(--primary);margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <div>
            <h3>Goshen Finance Plc — <?= ucfirst(str_replace('_', ' ', $report['report_type'])) ?></h3>
            <p style="color:var(--gray);font-size:0.85rem;margin-top:5px;">
                Generated: <?= formatDateTime($report['date_generated']) ?> | 
                Author: <?= sanitize($report['author_name']) ?> |
                Report ID: #<?= $report['id'] ?>
            </p>
        </div>
        <div style="display:flex;gap:8px;">
            <button onclick="window.print()" class="btn btn-sm btn-warning">🖨️ Print</button>
            <a href="<?= BASE_URL ?>/reports/index.php" class="btn btn-sm btn-info">← Back</a>
        </div>
    </div>

    <div style="background:#fff;border:1px solid var(--border);border-radius:6px;padding:25px;">
        <div style="text-align:center;border-bottom:2px solid var(--primary);padding-bottom:15px;margin-bottom:20px;">
            <strong style="color:var(--primary);font-size:1.1rem;">GOSHEN FINANCE PLC</strong><br>
            <small style="color:var(--gray);">Confidential Financial Report | Authorized by MINICOM | Est. 2005</small>
        </div>
        <pre style="white-space:pre-wrap;font-family:'Courier New',monospace;font-size:0.9rem;line-height:1.7;"><?= sanitize($report['data']) ?></pre>
        <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:15px;text-align:center;">
            <small style="color:var(--gray);">© <?= date('Y') ?> Goshen Finance Plc — AI-Based FinOps MIS</small>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
