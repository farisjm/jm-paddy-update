# 🌾 JM PADDY
### Paddy & Farmer Billing Management System
**Simple • Fast • Accurate**

A responsive PHP/MySQL billing system for paddy purchases, farmer balances, payment tracking, receipts and reports.

## Features
- Fresh responsive light-green UI for desktop and mobile
- Create and edit bills; delete bills with confirmation
- Automatic weight totals, district divisor, net weight and bill amount
- Configurable district divisors and default paddy prices in **Rates & Prices**
- Farmer previous outstanding balance carried forward to future bills
- Payment received at bill creation plus later partial payments
- Balance formula: **previous balance + current bill amount − all payments**
- Bill history with bill view, edit and delete actions
- Printable receipts; browser print dialog supports **Save as PDF**
- WhatsApp message prepared with bill total, payments and balance due
- Farmer directory, dashboard and district reports

## Default calculation
- Grand total = sum of all entered weights
- Net weight = grand total ÷ district divisor
- Bill amount = net weight × price per net kg
- Remaining balance = previous outstanding + bill amount − payment(s)

Default district divisors (editable):
| District | Divisor |
|---|---:|
| Vavuniya | 72 |
| Mannar | 73 |
| Mullaitivu | 73 |
| Kilinochchi | 73 |
| Jaffna | 73 |

## Setup
1. Install XAMPP and start Apache and MySQL.
2. Copy the repository into `C:\xampp\htdocs\jm-paddy`.
3. In phpMyAdmin, create and select the database `jm_paddy`.
4. For a new database, import `database_schema.sql` first to create the base tables. If you already have tables and bills, keep your data and skip this step.
5. Import **`upgrade_v2.sql`** into `jm_paddy`. It adds balance/payment columns and creates the district rate and paddy price tables. It is designed to be rerun on supported MariaDB/MySQL versions.
6. Confirm database credentials in `db.php`.
7. Open `http://localhost/jm-paddy/dashboard.php`.

## Important notes
- The app does not send WhatsApp messages silently. The WhatsApp button opens a prefilled message and the user must tap **Send**. Fully automatic sending requires an approved WhatsApp Business Platform/API setup.
- Print / Save PDF opens the browser print dialog; choose **Save as PDF**.
- Deleting a bill also deletes payment entries attached to that bill. This cannot be undone; export/back up your database first.
- Test the migration and workflows on a backup copy before using real financial records. The project has not been tested against every possible existing database schema.
- Public deployment needs authentication/authorization, CSRF protection, audit logs, secure database credentials and scheduled backups. Do not expose the local XAMPP configuration directly to the internet.

## Technology
PHP 8+, MySQL/MariaDB, HTML, CSS, JavaScript, XAMPP.

## Developer
M. J. M. FARIS — BSc Information Technology Undergraduate, University of Vavuniya.
