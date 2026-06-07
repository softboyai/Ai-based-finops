<?php
/**
 * Installation Script for Goshen Finance Plc - FinOps MIS
 * Run this file once to set up the database and default admin user.
 * 
 * After installation, delete this file for security.
 */

// Compute base path for this install file
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $scriptDir);

$host = 'localhost';
$username = 'root';
$password = '';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS goshen_finops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE goshen_finops");

        // Settings table - institution info
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Insert default settings
        $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
            ('institution_name', 'Goshen Finance Plc'),
            ('founded_year', '2005'),
            ('authorization', 'MINICOM'),
            ('location', 'Kigali, Rwanda'),
            ('currency', 'Rwf'),
            ('currency_code', 'RWF'),
            ('date_format', 'd/m/Y'),
            ('tagline', 'AI-Based FinOps Management Information System'),
            ('contact_email', 'info@goshenfinance.rw'),
            ('contact_phone', '+250 788 000 000')
        ");

        // Users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'finance_officer', 'management') NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Customers table with Goshen-specific account types
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            account_number VARCHAR(20) NOT NULL UNIQUE,
            account_type ENUM('savings', 'loan', 'investment', 'current') NOT NULL,
            balance DECIMAL(15,2) DEFAULT 0.00,
            opening_balance DECIMAL(15,2) DEFAULT 0.00,
            date_opened DATE NOT NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Transactions table
        $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            type ENUM('deposit', 'withdrawal', 'loan_repayment') NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            date DATETIME DEFAULT CURRENT_TIMESTAMP,
            processed_by INT NOT NULL,
            notes TEXT,
            FOREIGN KEY (customer_id) REFERENCES customers(id),
            FOREIGN KEY (processed_by) REFERENCES users(id)
        )");

        // Risk alerts table
        $pdo->exec("CREATE TABLE IF NOT EXISTS risk_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id INT NOT NULL,
            customer_id INT NOT NULL,
            flag_reason VARCHAR(255) NOT NULL,
            risk_score ENUM('Low', 'Medium', 'High') NOT NULL,
            flagged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reviewed TINYINT(1) DEFAULT 0,
            FOREIGN KEY (transaction_id) REFERENCES transactions(id),
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        )");

        // Reports table
        $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_type VARCHAR(50) NOT NULL,
            generated_by INT NOT NULL,
            date_generated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data LONGTEXT,
            FOREIGN KEY (generated_by) REFERENCES users(id)
        )");

        // Loan repayments table
        $pdo->exec("CREATE TABLE IF NOT EXISTS loan_repayments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            due_date DATE NOT NULL,
            amount_due DECIMAL(15,2) NOT NULL,
            amount_paid DECIMAL(15,2) DEFAULT 0.00,
            status ENUM('pending', 'paid', 'missed', 'partial') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        )");

        // Upgrade: add email column if it doesn't exist (for existing installations)
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) NOT NULL DEFAULT '' AFTER name");
        } catch (PDOException $e) {
            // Column already exists, ignore
        }

        // Create default admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['System Administrator', 'admin@goshenfinance.rw', 'admin', $adminPassword, 'admin', 'active']);

        $message = "Installation successful! Database 'goshen_finops' created with all tables. Default login: username = <strong>admin</strong>, password = <strong>admin123</strong>. Please delete this file after setup.";

    } catch (PDOException $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Goshen Finance Plc | FinOps MIS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <h1>Goshen Finance Plc</h1>
        <p class="subtitle">AI-Based FinOps MIS — Installation</p>
        <p style="text-align:center;font-size:0.8rem;color:var(--gray);margin-bottom:20px;">
            Established 2005 | Authorized by MINICOM | Kigali, Rwanda
        </p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary">Go to Login</a>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <form method="POST"><button type="submit" class="btn btn-primary">Retry Installation</button></form>
        <?php else: ?>
            <p style="margin-bottom:20px;color:var(--gray);font-size:0.9rem;">
                This will create the <strong>goshen_finops</strong> database with all required tables and default settings for Goshen Finance Plc.
                Ensure MySQL is running on XAMPP.
            </p>
            <form method="POST">
                <button type="submit" class="btn btn-primary">Install Database</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
