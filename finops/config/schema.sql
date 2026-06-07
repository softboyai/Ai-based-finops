CREATE DATABASE IF NOT EXISTS goshen_finops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE goshen_finops;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (setting_key, setting_value) VALUES
('institution_name', 'Goshen Finance Plc'),
('founded_year', '2005'),
('authorization', 'MINICOM'),
('location', 'Kigali, Rwanda'),
('currency', 'Rwf'),
('currency_code', 'RWF'),
('date_format', 'd/m/Y'),
('tagline', 'AI-Based FinOps Management Information System'),
('contact_email', 'info@goshenfinance.rw'),
('contact_phone', '+250 788 000 000');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'finance_officer', 'management') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    account_number VARCHAR(20) NOT NULL UNIQUE,
    account_type ENUM('savings', 'loan', 'investment', 'current') NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    opening_balance DECIMAL(15,2) DEFAULT 0.00,
    date_opened DATE NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'loan_repayment') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_by INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

CREATE TABLE risk_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    customer_id INT NOT NULL,
    flag_reason VARCHAR(255) NOT NULL,
    risk_score ENUM('Low', 'Medium', 'High') NOT NULL,
    flagged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    generated_by INT NOT NULL,
    date_generated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data LONGTEXT,
    FOREIGN KEY (generated_by) REFERENCES users(id)
);

CREATE TABLE loan_repayments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    due_date DATE NOT NULL,
    amount_due DECIMAL(15,2) NOT NULL,
    amount_paid DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('pending', 'paid', 'missed', 'partial') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Default admin user (password set via install.php)
