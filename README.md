# 🌾 JM PADDY
### Paddy & Farmer Billing Management System
**Simple • Fast • Accurate**

A PHP and MySQL web application for paddy weight calculation, farmer billing, receipt printing and basic business summaries.

## Current features
- Responsive light-green and white UI with dark-green navigation
- Dashboard with live bill count, farmer count and total bill value
- Create bills with farmer details, district-based divisor and paddy type
- Dynamic weight entry with automatic subtotals and client-side calculation
- Server-side validation and recalculation when saving bills
- Transaction-based saving of farmer, bill and weight records
- Bill history with links to printable receipts
- Print or save receipts as PDF using the browser print dialog
- WhatsApp bill summary link when a farmer phone number is supplied
- Farmer directory and name/phone search
- District-level billing summary reports
- District divisor reference and application setup information

## Calculation rules
- Grand total = sum of entered weights
- Net weight = grand total ÷ district divisor
- Total amount = net weight × price per net kg

| District | Divisor |
|---|---:|
| Vavuniya | 72 |
| Mannar | 73 |
| Mullaitivu | 73 |
| Kilinochchi | 73 |
| Jaffna | 73 |

## Tech stack
PHP 8+, MySQL/MariaDB, HTML, CSS, JavaScript, XAMPP.

## Local setup
1. Install XAMPP and start Apache and MySQL.
2. Copy this repository into `C:\xampp\htdocs\jm-paddy`.
3. Open phpMyAdmin and create database `jm_paddy`.
4. Create the tables `farmers`, `bills` and `bill_weights` using the schema required by the PHP pages.
5. Check the local connection values in `db.php`.
6. Open `http://localhost/jm-paddy/dashboard.php`.

## Important current limitations
This is still under development. Editing/deleting existing bills, recording partial payments, previous-balance carry-forward, editable database-backed price lists, login/roles, and database backup are not implemented yet. Farmer records are created when a bill is saved; the directory currently supports searching and viewing, not editing/deleting. The project needs testing against the actual local database schema before production use.

## Security
Do not publish production database credentials. Before deploying publicly, add authentication and authorization, CSRF protection, audit logging, secure database credentials and backups.

## Developer
M. J. M. FARIS — BSc Information Technology Undergraduate, University of Vavuniya.
