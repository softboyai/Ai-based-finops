<?php
$host = 'localhost';
$dbname = 'goshen_finops';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

session_start();

// Auto-detect the base URL path so links work regardless of folder name
function getBasePath() {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Navigate up to find the finops root
    if (preg_match('/(.*\/finops)/', $scriptDir, $matches)) {
        return $matches[1];
    }
    return $scriptDir;
}

define('BASE_URL', getBasePath());

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function formatCurrency($amount) {
    return 'Rwf ' . number_format($amount, 0, '.', ',');
}

function getInstitutionName() {
    return 'Goshen Finance Plc';
}

function getInstitutionInfo() {
    return [
        'name' => 'Goshen Finance Plc',
        'founded' => 2005,
        'authorization' => 'MINICOM',
        'location' => 'Rwanda',
        'currency' => 'Rwf',
        'tagline' => 'AI-Based FinOps Management Information System'
    ];
}
