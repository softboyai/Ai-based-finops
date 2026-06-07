<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Goshen Finance Plc' ?> - Goshen Finance Plc | FinOps MIS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-bar">
            <h2><?= $pageTitle ?? 'Dashboard' ?></h2>
            <div class="user-info">
                <span><?= sanitize($_SESSION['name']) ?> (<?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?>)</span>
                <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            </div>
        </div>
        <div class="content">
