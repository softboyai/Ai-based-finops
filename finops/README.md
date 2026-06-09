# Goshen Finance Plc — AI-Based FinOps Management Information System

## 📋 What Is This Project?

This is an **AI-Based Financial Operations (FinOps) Management Information System** built for **Goshen Finance Plc**, a microfinance institution established in **2005**, authorized by **MINICOM** (Ministry of Trade and Industry), operating in **Kigali, Rwanda**.

The system manages:
- Customer accounts (Savings, Loan, Investment, Current)
- Financial transactions (Deposits, Withdrawals, Loan Repayments)
- Loan lifecycle management
- Financial reporting (with downloadable PDF)
- **AI-powered risk detection and financial insights**

---

## 🤖 How Is This AI-Based?

This system implements **Artificial Intelligence** using rule-based expert systems and statistical analysis algorithms directly in PHP. No external Python or ML libraries are needed.

### AI Components:

#### 1. AI Risk Detection Engine (`ai/risk_engine.php`)
After **every single transaction**, the AI engine automatically runs these detection algorithms:

| Algorithm | What It Does | How It Works | Risk Level |
|-----------|-------------|--------------|-----------|
| **Anomaly Detection** | Detects unusually large transactions | If amount > 3× customer's historical average → flag | HIGH |
| **Frequency Analysis** | Detects rapid-fire transactions | If 3+ transactions from same account within 1 hour → flag | MEDIUM |
| **Balance Threshold** | Detects dangerously low balances | If balance drops below 10% of opening balance → flag | HIGH |

#### 2. AI Risk Scoring
- Each flag gets a weighted score: High=3, Medium=2, Low=1
- Scores accumulate per customer
- System ranks **Top 5 Riskiest Accounts** automatically

#### 3. AI Loan Default Prediction
- Monitors loan repayment history
- Flags customers with **2+ missed repayments** as high default probability
- Displayed on AI Insights dashboard

#### 4. AI Trend Analysis
- Compares current month vs last month transaction volume
- Calculates % change (growth/decline)
- Generates 6-month trend visualization

#### 5. AI Performance Assessment (Management Dashboard)
- Automatically evaluates KPIs
- Generates health warnings:
  - "Loan repayment rate below 70%"
  - "More than 5 high-risk alerts pending"
  - "Transaction volume dropped 20%+"
  - "Customer base shrinking"

### Why This Qualifies as AI:
1. **Rule-based Expert System** — mimics how a human fraud analyst would flag suspicious activity
2. **Statistical Anomaly Detection** — deviation from mean behavior (3x standard)
3. **Predictive Scoring** — accumulated risk scores predict which accounts need intervention
4. **Automated Decision Making** — system autonomously flags, scores, and categorizes without human input
5. **Pattern Recognition** — frequency and trend analysis identifies behavioral patterns
6. **Continuous Learning** — as more transactions occur, averages update, making detection more accurate

---

## 🚀 How to Run This Project (Step by Step)

### Prerequisites
- **XAMPP** installed (download: https://www.apachefriends.org/)

### Step 1: Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache** → should turn green
3. Click **Start** next to **MySQL** → should turn green

### Step 2: Place the Project
The project folder should be inside XAMPP's htdocs:
```
C:\xampp\htdocs\AI-Based FinOps\finops\
```

### Step 3: Install the Database
1. Open browser
2. Go to: `http://localhost/AI-Based%20FinOps/finops/install.php`
3. Click **"Install Database"**
4. Wait for success message

### Step 4: Seed Sample Data (IMPORTANT for AI demo)
1. Go to: `http://localhost/AI-Based%20FinOps/finops/seed.php`
2. Click **"🚀 Seed Sample Data"**
3. This populates customers, transactions, and **triggers the AI engine**
4. After this, all AI features (charts, risk alerts, insights) will have data

### Step 5: Login
Go to: `http://localhost/AI-Based%20FinOps/finops/`

**IMPORTANT:** If you get a "too many redirects" error, clear your browser cookies for localhost first (`Ctrl+Shift+Delete` → Cookies), or open an Incognito window.

**Login credentials:**

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator (full access) |
| jbaptiste | officer123 | Finance Officer (operations) |
| phabimana | manage123 | Management Staff (monitoring) |

### Step 6: Clean Up
Delete these files after setup (security):
- `install.php`
- `seed.php`

---

## 🎤 How to Present This Project

### Presentation Script (5-7 minutes):

**1. Introduction (1 min)**
> "This is an AI-Based FinOps Management Information System built for Goshen Finance Plc, a microfinance institution in Kigali, Rwanda. It uses artificial intelligence to detect financial risks, predict loan defaults, and provide intelligent insights to management."

**2. Login & Homepage (30 sec)**
- Show the homepage with AI features highlighted
- Login as admin to show full access

**3. Demo the AI Features (3 min)**
- **AI Risk Alerts**: Show flagged transactions with color-coded risk levels (red=High, orange=Medium, green=Low)
- **AI Insights**: Show the charts — monthly trends, top risk accounts, loan default predictions
- **Explain**: "After every transaction, the AI engine checks 3 rules: anomaly detection, frequency analysis, and balance threshold monitoring. It automatically assigns risk scores."

**4. Show Transactions (1 min)**
- Process a new payment (deposit)
- Show how balance updates automatically
- Mention: "The AI engine just ran in the background"

**5. Reports (1 min)**
- Show financial reports
- Click "Download PDF" to show the branded report
- Show the Goshen Finance Plc branding on the PDF

**6. Role-based Access (30 sec)**
- Logout and login as `phabimana` (Management)
- Show Performance Monitor with KPIs
- "Management can monitor but cannot modify data"

**7. Conclusion (30 sec)**
> "The system provides intelligent, automated financial monitoring. The AI detects anomalies humans might miss, predicts loan defaults before they happen, and helps management make data-driven decisions."

### Key Points to Emphasize:
- "AI runs automatically after every transaction — no manual intervention"
- "Risk scoring accumulates over time — gets smarter with more data"
- "Loan default prediction identifies at-risk customers early"
- "All built with PHP — no external AI libraries needed"
- "Color-coded alerts: red = urgent, orange = watch, green = safe"

---

## 📥 How to Feed Content / Add Data

### Method 1: Use the Seed Script (Bulk Data)
Run `seed.php` to populate 10 customers, 30+ transactions, and trigger all AI features.

### Method 2: Manual Data Entry (Through the System)

**Add Customers:**
1. Login as admin or finance officer
2. Sidebar → Customers → + Add Customer
3. Fill: Name, Account Number (e.g. GF-SAV-004), Type, Opening Balance

**Record Transactions:**
1. Sidebar → Process Payment
2. Select customer, type (Deposit/Withdrawal/Loan Repayment), enter amount
3. Click "Process Transaction"
4. AI engine runs automatically and flags if suspicious

**Add Loan Schedules:**
1. Sidebar → Loan Management
2. Select a loan customer, set due date and amount
3. Mark as Paid or Missed (missed payments trigger AI loan default alert)

**Generate Reports:**
1. Sidebar → Generate Report
2. Select type and date range
3. System auto-compiles the data

### Method 3: Direct Database (phpMyAdmin)
1. Go to `http://localhost/phpmyadmin`
2. Select database `goshen_finops`
3. Insert rows directly into tables
4. Note: This skips the AI engine (transactions won't be auto-analyzed)

---

## 🎨 How to Change Colors

All colors are defined in one place: `assets/css/style.css` at the top.

Open the file and find the `:root` section (line 6-17):

```css
:root {
    --primary: #003366;        /* Main dark blue — sidebar, headers, buttons */
    --primary-light: #004080;  /* Lighter blue — hover states */
    --primary-dark: #002244;   /* Darker blue — accents */
    --white: #ffffff;          /* Background */
    --light-gray: #f4f6f9;    /* Page background */
    --gray: #6c757d;          /* Secondary text */
    --border: #dee2e6;        /* Borders */
    --success: #28a745;       /* Green — deposits, positive */
    --warning: #ffc107;       /* Yellow — medium risk */
    --danger: #dc3545;        /* Red — high risk, withdrawals */
    --orange: #fd7e14;        /* Orange — medium alerts */
    --info: #17a2b8;          /* Teal — information */
}
```

**To change the main color scheme:**
- Change `--primary: #003366` to any hex color
- Example for green theme: `--primary: #1b5e20`
- Example for purple theme: `--primary: #4a148c`
- Example for red theme: `--primary: #b71c1c`

**To change risk alert colors:**
- `--danger` = High risk color (red)
- `--warning` = Medium risk color (yellow)
- `--success` = Low risk / positive color (green)

---

## ✏️ How to Change the Institution Name

If you want to rename from "Goshen Finance Plc" to another name:

### 1. Change in Settings (Admin Panel)
- Login as admin → Sidebar → Settings
- Change "Institution Name" field
- Click Save

### 2. Change in Code (for complete rebranding)
Edit `config/db.php` — update the `getInstitutionName()` and `getInstitutionInfo()` functions:
```php
function getInstitutionName() {
    return 'Your Company Name';
}
```

Also update `includes/sidebar.php` — change the `<h2>` text.
And `index.php` (homepage) — change the `<h1>` text.

### 3. Change the Logo
Replace the file `assets/images/goshen.png` with your own logo (keep the same filename, or update references in sidebar.php, login.php, and index.php).

---

## 📁 Project Structure

```
finops/
├── config/
│   ├── db.php                  ← Database connection, helpers, BASE_URL
│   └── schema.sql              ← Raw SQL reference
├── auth/
│   ├── login.php               ← Login with role-based redirect
│   └── logout.php              ← Session destroy
├── admin/
│   ├── dashboard.php           ← Admin dashboard (stats + charts)
│   ├── users.php               ← User CRUD with full validation
│   └── settings.php            ← Institution settings
├── finance/
│   ├── dashboard.php           ← Finance Officer dashboard
│   └── update_report.php       ← Create/edit financial reports
├── management/
│   ├── dashboard.php           ← Management overview with KPIs
│   └── performance.php         ← Full performance monitor
├── customers/
│   ├── index.php               ← Customer list + search
│   ├── add.php                 ← Add/edit customer
│   └── view.php                ← Customer detail + history
├── transactions/
│   ├── index.php               ← Transaction list + filters
│   └── add.php                 ← Process payment (triggers AI)
├── loans/
│   └── index.php               ← Loan repayment management
├── reports/
│   ├── index.php               ← View financial reports
│   ├── generate.php            ← Auto-generate reports from data
│   ├── download.php            ← PDF-ready branded reports
│   └── view_report.php         ← View saved report detail
├── ai/
│   ├── risk_engine.php         ← ★ ALL AI LOGIC LIVES HERE ★
│   ├── risk_alerts.php         ← Flagged transactions dashboard
│   └── insights.php            ← AI trends + predictions + charts
├── includes/
│   ├── header.php              ← Page header + Chart.js CDN
│   ├── footer.php              ← Page footer + JS
│   └── sidebar.php             ← Role-aware navigation
├── assets/
│   ├── css/style.css           ← All styles (colors here!)
│   ├── js/main.js              ← Client-side utilities
│   └── images/goshen.png       ← Company logo
├── index.php                   ← Homepage / landing page
├── install.php                 ← Database installer
├── seed.php                    ← Sample data loader
└── README.md                   ← This file
```

---

## 👥 User Roles — What Each Does

| Role | Can Do | Cannot Do |
|------|--------|-----------|
| **Administrator** | Everything: manage users, settings, review alerts, all operations | — |
| **Finance Officer** | Process payments, manage customers, loans, generate reports, update reports | Manage users, change settings |
| **Management Staff** | Monitor performance KPIs, view reports, view AI insights, view risk alerts | Modify any data |

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 7.4+ (pure, no frameworks) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Charts | Chart.js (loaded via CDN) |
| Server | XAMPP (Apache + MySQL) |
| AI Engine | PHP rule-based expert system |
| Currency | Rwandan Francs (Rwf) |
| Date Format | DD/MM/YYYY |

---

## 🔐 Security

- Passwords: `password_hash()` + `password_verify()` (bcrypt)
- SQL: PDO prepared statements (prevents injection)
- XSS: `htmlspecialchars()` on all outputs
- Sessions: Checked on every protected page
- Roles: Server-side enforcement (not just UI hiding)
- Validation: Server + client-side for all forms

---

## ❓ Troubleshooting

| Problem | Solution |
|---------|----------|
| MySQL won't start | Close other MySQL instances, or change port in `my.ini` |
| "Column not found: email" | Run install.php again — it adds the missing column |
| Logo not showing | Clear browser cache. Paths auto-detect via BASE_URL. |
| Charts empty | Run seed.php to populate data |
| AI alerts empty | Process transactions or run seed.php — AI only flags after transactions exist |
| Page not found | Make sure URL uses: `http://localhost/AI-Based%20FinOps/finops/` |
| "Too many redirects" | Clear cookies for localhost (`Ctrl+Shift+Delete`) or use Incognito |
| "Undefined array key role" | Clear cookies — old session data conflict. Incognito fixes it. |
| Login doesn't work | Clear cookies first, then login. Password for admin is `admin123` |

---

## 📄 License

© 2025 Goshen Finance Plc. Built as an academic project demonstrating AI-based financial management systems.
