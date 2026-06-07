# PROJECT DOCUMENTATION
# AI-Based FinOps Management Information System
# Goshen Finance Plc

---

## COVER PAGE

| Field | Detail |
|-------|--------|
| **Project Title** | AI-Based FinOps Management Information System |
| **Institution** | Goshen Finance Plc |
| **Location** | Kigali, Rwanda |
| **Authorization** | MINICOM (Ministry of Trade and Industry) |
| **Year Founded** | 2005 |
| **Project Type** | Web-based Management Information System |
| **Technology** | PHP, MySQL, HTML/CSS/JavaScript, Chart.js |
| **AI Component** | Rule-based Expert System for Risk Detection |
| **Date** | 2025 |

---

## TABLE OF CONTENTS

1. Executive Summary
2. Introduction
   - 2.1 Background
   - 2.2 Problem Statement
   - 2.3 Objectives
   - 2.4 Scope
3. Literature Review
   - 3.1 Management Information Systems
   - 3.2 FinOps (Financial Operations)
   - 3.3 AI in Financial Systems
4. System Analysis
   - 4.1 Current System Analysis
   - 4.2 Requirements Analysis
   - 4.3 Feasibility Study
5. System Design
   - 5.1 System Architecture
   - 5.2 Database Design
   - 5.3 User Interface Design
   - 5.4 AI Algorithm Design
6. Implementation
   - 6.1 Development Environment
   - 6.2 Module Implementation
   - 6.3 AI Engine Implementation
   - 6.4 Security Implementation
7. Testing
   - 7.1 Testing Strategy
   - 7.2 Test Cases
   - 7.3 Results
8. User Guide
9. Conclusion & Recommendations
10. References

---

## 1. EXECUTIVE SUMMARY

Goshen Finance Plc, established in 2005 and authorized by MINICOM, provides microfinance services including savings accounts, loans, investments, and current accounts to customers in Rwanda. This project develops an AI-Based Financial Operations (FinOps) Management Information System that automates financial transaction processing, provides intelligent risk detection, predicts loan defaults, and generates actionable insights for management decision-making.

The system employs rule-based artificial intelligence algorithms to:
- Detect anomalous transactions in real-time
- Monitor transaction frequency patterns
- Flag accounts at financial risk
- Predict loan default probability
- Generate trend analysis and KPI assessments

Built using PHP, MySQL, and JavaScript with Chart.js for visualization, the system operates on XAMPP (Apache + MySQL) and requires no external AI libraries, making it lightweight and deployable on standard web hosting infrastructure.

---

## 2. INTRODUCTION

### 2.1 Background

Goshen Finance Plc is a microfinance institution operating in Kigali, Rwanda since 2005. The institution is authorized by MINICOM (Ministry of Trade and Industry) to provide financial services including:
- **Savings Accounts** — Personal and business savings
- **Loan Products** — Personal loans, business loans, group loans
- **Investment Accounts** — Fixed-term investments
- **Current Accounts** — Day-to-day transactional accounts

With a growing customer base and increasing transaction volumes, manual monitoring of financial operations has become insufficient. Suspicious transactions may go undetected, loan defaults may not be predicted early enough, and management lacks real-time visibility into operational performance.

### 2.2 Problem Statement

Goshen Finance Plc faces the following challenges:

1. **Manual Risk Detection** — Fraudulent or suspicious transactions are only identified after the fact, through manual audit, resulting in financial losses.
2. **Loan Default Surprise** — Loan officers only notice default patterns when payments are already significantly overdue.
3. **Lack of Real-time Insights** — Management relies on periodic manual reports which are outdated by the time they are reviewed.
4. **No Performance Monitoring** — There is no system to track finance officer productivity or operational KPIs.
5. **Paper-based Reports** — Financial reports are generated manually in spreadsheets, are time-consuming, and prone to errors.

### 2.3 Objectives

#### General Objective
To develop an AI-Based FinOps Management Information System that automates financial operations and provides intelligent risk detection for Goshen Finance Plc.

#### Specific Objectives
1. To design and implement a web-based system for managing customer accounts and financial transactions.
2. To develop AI algorithms for real-time anomaly detection, frequency analysis, and balance threshold monitoring.
3. To implement a loan default prediction system based on repayment history analysis.
4. To create an automated financial reporting module with downloadable PDF reports.
5. To build role-based dashboards for administrators, finance officers, and management staff.
6. To implement a performance monitoring system that tracks KPIs automatically.

### 2.4 Scope

**In Scope:**
- Customer account management (CRUD operations)
- Transaction processing (deposits, withdrawals, loan repayments)
- AI-based risk detection (3 algorithms)
- Loan repayment tracking and default prediction
- Financial report generation and PDF export
- Role-based access control (3 roles)
- Performance monitoring dashboard
- Chart.js data visualizations

**Out of Scope:**
- Mobile application
- Online banking / customer portal
- Integration with external banking APIs
- Machine learning model training
- SMS/Email notifications
- Multi-branch support

---

## 3. LITERATURE REVIEW

### 3.1 Management Information Systems (MIS)

A Management Information System (MIS) is a computer-based system that provides managers with the tools to organize, evaluate, and manage information for decision-making. In financial institutions, MIS serves as the backbone for:
- Recording transactions
- Generating reports
- Tracking performance metrics
- Supporting strategic decisions

The system developed here follows the MIS architecture with three information levels:
- **Operational Level** — Transaction processing (Finance Officers)
- **Tactical Level** — Performance reports (Management)
- **Strategic Level** — AI insights and trend analysis (Decision Makers)

### 3.2 FinOps (Financial Operations)

FinOps is a framework that combines financial management with operational technology. Key principles include:
- **Visibility** — All stakeholders can see financial data in real-time
- **Optimization** — Systems identify inefficiencies and suggest improvements
- **Automation** — Routine tasks are handled by the system, not humans

This project applies FinOps principles by automating transaction analysis, report generation, and risk detection.

### 3.3 Artificial Intelligence in Financial Systems

AI in finance typically falls into these categories:

| AI Type | Application | Used in This Project? |
|---------|-------------|----------------------|
| Rule-based Expert Systems | Fraud detection, compliance checking | ✅ Yes — Core AI engine |
| Statistical Analysis | Anomaly detection via deviation from mean | ✅ Yes — 3x average rule |
| Pattern Recognition | Identifying behavioral patterns | ✅ Yes — Frequency analysis |
| Predictive Analytics | Forecasting future events | ✅ Yes — Loan default prediction |
| Machine Learning | Self-improving models from training data | ❌ No — Beyond project scope |
| Deep Learning / Neural Networks | Complex pattern recognition | ❌ No — Requires large datasets |

The rule-based expert system approach was chosen because:
1. It is transparent — decisions can be explained
2. It requires no training data
3. It can be implemented in PHP without external libraries
4. It provides immediate results from the first transaction
5. Rules can be easily modified by administrators

---

## 4. SYSTEM ANALYSIS

### 4.1 Current System Analysis

| Aspect | Current State | Proposed System |
|--------|--------------|-----------------|
| Transaction recording | Manual ledger / spreadsheet | Automated with database |
| Risk detection | Manual audit (weekly/monthly) | Real-time AI after every transaction |
| Loan monitoring | Manual follow-up | Automated default prediction |
| Reporting | Excel spreadsheets | Auto-generated with PDF export |
| Performance tracking | None | KPI dashboard with metrics |
| Access control | Physical documents | Role-based digital access |

### 4.2 Requirements Analysis

#### Functional Requirements

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-01 | System shall authenticate users with username/password | High |
| FR-02 | System shall enforce role-based access (Admin, Finance Officer, Management) | High |
| FR-03 | System shall allow CRUD operations on customer accounts | High |
| FR-04 | System shall process deposits, withdrawals, and loan repayments | High |
| FR-05 | System shall run AI risk analysis after every transaction | High |
| FR-06 | System shall flag transactions exceeding 3x customer average | High |
| FR-07 | System shall flag 3+ transactions within 1 hour from same account | High |
| FR-08 | System shall flag accounts with balance below 10% of opening balance | High |
| FR-09 | System shall assign risk scores (High/Medium/Low) | High |
| FR-10 | System shall predict loan defaults (2+ missed payments) | Medium |
| FR-11 | System shall generate financial reports (monthly, balance, income/expense) | Medium |
| FR-12 | System shall export reports as downloadable PDF | Medium |
| FR-13 | System shall display charts and trend visualizations | Medium |
| FR-14 | System shall monitor finance officer performance | Medium |
| FR-15 | System shall track KPIs (customer growth, transaction volume, repayment rate) | Medium |

#### Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-01 | Passwords must be encrypted using bcrypt |
| NFR-02 | All inputs must be validated (server + client side) |
| NFR-03 | System must prevent SQL injection (prepared statements) |
| NFR-04 | System must be responsive (mobile-friendly) |
| NFR-05 | Date format must be DD/MM/YYYY (Rwanda standard) |
| NFR-06 | Currency must be displayed in Rwandan Francs (Rwf) |
| NFR-07 | System must work on XAMPP (Apache + MySQL) |

### 4.3 Feasibility Study

| Feasibility Type | Assessment |
|------------------|-----------|
| **Technical** | ✅ Feasible — PHP, MySQL, and JavaScript are well-established. XAMPP provides a free development environment. No special hardware required. |
| **Economic** | ✅ Feasible — Uses only open-source, free tools. No licensing costs. Can run on any standard computer. |
| **Operational** | ✅ Feasible — Staff can be trained within 1-2 hours. Interface is intuitive with color-coded indicators. |
| **Schedule** | ✅ Feasible — Can be developed in 4-6 weeks with all modules. |

---

## 5. SYSTEM DESIGN

### 5.1 System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                      │
│     HTML/CSS/JavaScript + Chart.js (Browser)             │
├─────────────────────────────────────────────────────────┤
│                   APPLICATION LAYER                       │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌───────────┐ │
│  │   Auth   │ │Customers │ │ Transact │ │  Reports  │ │
│  │  Module  │ │  Module  │ │  Module  │ │  Module   │ │
│  └──────────┘ └──────────┘ └──────────┘ └───────────┘ │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐               │
│  │   Loan   │ │   User   │ │    AI    │               │
│  │  Module  │ │  Module  │ │  Engine  │ ←── AI Layer  │
│  └──────────┘ └──────────┘ └──────────┘               │
├─────────────────────────────────────────────────────────┤
│                     DATA LAYER                            │
│              MySQL Database (goshen_finops)               │
│  ┌────────┐ ┌───────────┐ ┌───────────┐ ┌──────────┐  │
│  │ users  │ │ customers │ │transactions│ │risk_alerts│  │
│  └────────┘ └───────────┘ └───────────┘ └──────────┘  │
│  ┌────────┐ ┌───────────┐ ┌───────────┐               │
│  │reports │ │loan_repay │ │ settings  │               │
│  └────────┘ └───────────┘ └───────────┘               │
└─────────────────────────────────────────────────────────┘
```

**Architecture Pattern:** 3-Tier (Presentation → Application → Data)

### 5.2 Database Design

#### Entity-Relationship Summary

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| **users** | System users/operators | id, name, email, username, password, role, status |
| **customers** | Bank account holders | id, name, account_number, account_type, balance, opening_balance |
| **transactions** | All financial movements | id, customer_id, type, amount, date, processed_by |
| **risk_alerts** | AI-generated flags | id, transaction_id, customer_id, flag_reason, risk_score |
| **loan_repayments** | Loan schedule tracking | id, customer_id, due_date, amount_due, amount_paid, status |
| **reports** | Saved financial reports | id, report_type, generated_by, data |
| **settings** | Institution configuration | id, setting_key, setting_value |

#### Relationships
- `transactions.customer_id` → `customers.id` (Many-to-One)
- `transactions.processed_by` → `users.id` (Many-to-One)
- `risk_alerts.transaction_id` → `transactions.id` (Many-to-One)
- `risk_alerts.customer_id` → `customers.id` (Many-to-One)
- `loan_repayments.customer_id` → `customers.id` (Many-to-One)
- `reports.generated_by` → `users.id` (Many-to-One)

### 5.3 User Interface Design

#### Design Principles
- **Color Scheme:** Dark blue (#003366) + white — professional and trustworthy
- **Layout:** Fixed sidebar + scrollable main content
- **Risk Colors:** Red (High), Orange (Medium), Green (Low)
- **Typography:** Segoe UI — clean, modern, readable
- **Charts:** Chart.js via CDN — bar, line, doughnut charts

#### Role-Based UI

| Role | Dashboard | Sidebar Links |
|------|-----------|---------------|
| Admin | Full stats + charts + alerts | All modules + User Management + Settings |
| Finance Officer | Personal stats + quick actions | Customers, Transactions, Loans, Reports |
| Management | KPIs + trend charts | Reports, Performance Monitor, AI Insights |

### 5.4 AI Algorithm Design

#### Algorithm 1: Anomaly Detection (Amount)
```
FUNCTION detectAnomaly(transaction):
    average = SELECT AVG(amount) FROM transactions WHERE customer_id = X
    IF transaction.amount > (3 × average):
        CREATE risk_alert(reason="Amount exceeds 3x average", score="High")
```

#### Algorithm 2: Frequency Analysis
```
FUNCTION detectFrequency(transaction):
    count = SELECT COUNT(*) FROM transactions 
            WHERE customer_id = X 
            AND date WITHIN 1 HOUR of transaction.date
    IF count >= 3:
        CREATE risk_alert(reason="High frequency", score="Medium")
```

#### Algorithm 3: Balance Threshold
```
FUNCTION detectLowBalance(transaction):
    IF customer.balance < (0.10 × customer.opening_balance):
        CREATE risk_alert(reason="Balance below 10% of opening", score="High")
```

#### Algorithm 4: Loan Default Prediction
```
FUNCTION predictDefault(customer):
    missed = SELECT COUNT(*) FROM loan_repayments 
             WHERE customer_id = X AND status = 'missed'
    IF missed >= 2:
        RETURN "HIGH DEFAULT RISK"
```

---

## 6. IMPLEMENTATION

### 6.1 Development Environment

| Tool | Version | Purpose |
|------|---------|---------|
| XAMPP | 8.x | Apache + MySQL local server |
| PHP | 7.4+ | Backend scripting |
| MySQL/MariaDB | 10.x | Database engine |
| VS Code / Kiro | Latest | Code editor |
| Chrome | Latest | Testing browser |
| Chart.js | 4.x (CDN) | Chart rendering |

### 6.2 Module Implementation

| Module | Files | Lines of Code (approx) |
|--------|-------|----------------------|
| Authentication | login.php, logout.php | ~120 |
| Customer Management | index.php, add.php, view.php | ~350 |
| Transaction Processing | index.php, add.php | ~280 |
| Loan Management | index.php | ~200 |
| Financial Reports | index.php, generate.php, download.php, view_report.php | ~500 |
| AI Engine | risk_engine.php, risk_alerts.php, insights.php | ~400 |
| User Management | users.php | ~300 |
| Admin/Management Dashboards | dashboard.php, performance.php | ~400 |
| Shared Components | header.php, footer.php, sidebar.php, style.css, main.js | ~500 |
| **TOTAL** | **25+ files** | **~3,050+** |

### 6.3 AI Engine Implementation

The AI engine (`ai/risk_engine.php`) contains these key functions:

| Function | Purpose | Called When |
|----------|---------|------------|
| `analyzeTransaction($pdo, $transactionId)` | Runs all 3 detection rules | After every transaction |
| `calculateOverallRiskScore($pdo, $customerId)` | Sums weighted risk per customer | On demand |
| `getTopRiskAccounts($pdo, $limit)` | Ranks riskiest accounts | AI Insights page |
| `getMonthlyTrend($pdo)` | Compares month-over-month | Dashboards |
| `getLoanDefaultRisk($pdo)` | Lists high default-risk customers | AI Insights page |
| `getMonthlyTransactionData($pdo, $months)` | Gets chart data for N months | Charts |

### 6.4 Security Implementation

| Threat | Protection |
|--------|-----------|
| SQL Injection | PDO Prepared Statements throughout |
| XSS (Cross-Site Scripting) | `htmlspecialchars()` on all output |
| Password Theft | bcrypt hashing via `password_hash()` |
| Unauthorized Access | Session checks + role validation on every page |
| CSRF | POST-only forms for data modification |
| Brute Force | Account deactivation feature |

---

## 7. TESTING

### 7.1 Testing Strategy

- **Unit Testing** — Individual functions (AI algorithms) tested with known data
- **Integration Testing** — Transaction → AI Engine → Alert flow
- **User Acceptance Testing** — Role-based access verification
- **Security Testing** — SQL injection attempts, XSS attempts

### 7.2 Test Cases

| Test ID | Description | Expected Result | Status |
|---------|-------------|-----------------|--------|
| TC-01 | Login with correct credentials | Redirect to role dashboard | ✅ Pass |
| TC-02 | Login with wrong password | Error message shown | ✅ Pass |
| TC-03 | Access admin page as finance officer | Redirected to login | ✅ Pass |
| TC-04 | Add customer with valid data | Customer created | ✅ Pass |
| TC-05 | Add customer with duplicate account # | Error message | ✅ Pass |
| TC-06 | Process deposit | Balance increases, transaction recorded | ✅ Pass |
| TC-07 | Process withdrawal > balance | Error: insufficient funds | ✅ Pass |
| TC-08 | Transaction 4x average amount | AI flags as High risk | ✅ Pass |
| TC-09 | 3 transactions within 1 hour | AI flags as Medium risk | ✅ Pass |
| TC-10 | Balance drops below 10% | AI flags as High risk | ✅ Pass |
| TC-11 | Customer with 2 missed payments | Shows in loan default list | ✅ Pass |
| TC-12 | Generate PDF report | Opens print-ready page with branding | ✅ Pass |
| TC-13 | SQL injection in login form | Query sanitized, no injection | ✅ Pass |
| TC-14 | Name field with numbers | Validation error shown | ✅ Pass |
| TC-15 | Password without number | Validation error shown | ✅ Pass |

### 7.3 Results

All 15 test cases passed successfully. The AI engine correctly identifies:
- Anomalous transaction amounts (with 100% detection rate for >3x threshold)
- High-frequency transaction patterns
- Critical balance drops
- Loan default risk customers

---

## 8. USER GUIDE

### For Administrators
1. Login with admin credentials
2. Dashboard shows overall system health
3. Manage users: Admin → Users (add, edit, deactivate)
4. Review AI risk alerts: click "View All Alerts"
5. System settings: Admin → Settings (institution info)

### For Finance Officers
1. Login with finance officer credentials
2. Dashboard shows personal stats and quick actions
3. Add customers: Customers → + Add Customer
4. Process payments: Process Payment → Select customer, type, amount
5. Manage loans: Loan Management → Add schedule, mark paid/missed
6. Generate reports: Generate Report → Select type and date range
7. Update reports: Update Report → Add observations

### For Management Staff
1. Login with management credentials
2. Dashboard shows high-level KPIs
3. Monitor Performance: detailed KPIs, officer productivity, portfolio breakdown
4. View Reports: read financial reports generated by officers
5. AI Insights: trends, risk accounts, loan defaults
6. Risk Alerts: view all AI-flagged transactions

---

## 9. CONCLUSION & RECOMMENDATIONS

### Conclusion

The AI-Based FinOps Management Information System successfully meets all stated objectives:

1. ✅ Web-based system for customer and transaction management — implemented with full CRUD
2. ✅ AI algorithms for real-time risk detection — 3 algorithms running after every transaction
3. ✅ Loan default prediction — identifies customers with 2+ missed payments
4. ✅ Automated reporting with PDF export — 5 report types with branded download
5. ✅ Role-based dashboards — tailored for each user type
6. ✅ Performance monitoring — KPIs, officer tracking, AI health assessment

The system demonstrates that AI can be effectively implemented in financial systems using simple rule-based approaches without complex machine learning infrastructure.

### Recommendations for Future Development

1. **SMS/Email Alerts** — Notify officers immediately when AI flags a high-risk transaction
2. **Machine Learning** — Train models on historical data for improved prediction accuracy
3. **Mobile App** — Extend system to mobile devices for field officers
4. **Multi-Branch** — Support multiple Goshen Finance branch locations
5. **Customer Portal** — Allow customers to view their own account balances
6. **API Integration** — Connect to Rwanda's national payment systems
7. **Automated Reporting Schedule** — Generate and email weekly/monthly reports automatically

---

## 10. REFERENCES

1. Laudon, K.C. & Laudon, J.P. (2020). *Management Information Systems: Managing the Digital Firm*. 16th Edition. Pearson.
2. National Bank of Rwanda (2023). *Guidelines for Microfinance Institutions*.
3. MINICOM Rwanda (2022). *Regulations for Financial Service Providers*.
4. Russell, S. & Norvig, P. (2021). *Artificial Intelligence: A Modern Approach*. 4th Edition. Pearson.
5. PHP Documentation (2024). *PDO - PHP Data Objects*. https://www.php.net/manual/en/book.pdo.php
6. Chart.js (2024). *Documentation*. https://www.chartjs.org/docs/
7. OWASP Foundation (2023). *Top 10 Web Application Security Risks*. https://owasp.org/www-project-top-ten/
8. FinOps Foundation (2023). *What is FinOps?*. https://www.finops.org/introduction/what-is-finops/

---

## APPENDICES

### Appendix A: Default Login Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| jbaptiste | officer123 | Finance Officer |
| mcmukamana | officer123 | Finance Officer |
| phabimana | manage123 | Management Staff |

### Appendix B: Database Schema (SQL)

See file: `config/schema.sql`

### Appendix C: AI Risk Engine Source Code

See file: `ai/risk_engine.php`

### Appendix D: Color Scheme

| Element | Color Code | Usage |
|---------|-----------|-------|
| Primary | #003366 | Sidebar, headers, buttons |
| Success/Low Risk | #28a745 | Deposits, positive indicators |
| Warning/Medium Risk | #ffc107 | Caution indicators |
| Danger/High Risk | #dc3545 | Withdrawals, alerts |
| Info | #17a2b8 | Information panels |
| Background | #f4f6f9 | Page background |

---

*Document prepared for Goshen Finance Plc — AI-Based FinOps MIS Project*
*Kigali, Rwanda — 2025*
