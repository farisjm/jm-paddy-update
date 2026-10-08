# 🌾 JM PADDY

### Paddy & Farmer Billing Management System

**Simple • Fast • Accurate**

JM PADDY is a web-based Paddy & Farmer Billing Management System designed to simplify paddy purchasing, farmer information management, weight calculation, billing, and bill history.

## 🚀 Features

* 🔐 Login System
* 📊 Dashboard
* 🧾 Create Paddy Bills
* 👨‍🌾 Farmer Information Management
* 📍 District-based Rate Calculation
* 🌾 Paddy Type Selection
* 💰 Manual Paddy Price Entry
* ⚖️ Dynamic Weight Entry
* ➕ Automatic Column Subtotals
* 🧮 Automatic Grand Total Calculation
* 📋 Bill History
* 👁️ View Saved Bills
* 🖨️ Print Bills
* 📄 PDF Bill Generation
* 📱 Responsive Design for Mobile and Desktop
* 💾 MySQL Database

## 🧮 Calculation

The system calculates the bill using the following formulas:

**Grand Total**

```text
Grand Total = Weight 1 + Weight 2 + ... + Weight N
```

**Net Weight**

```text
Net Weight = Grand Total ÷ District Rate
```

**Total Amount**

```text
Total Amount = Net Weight × Paddy Price
```

### District Rates

| District    | Rate |
| ----------- | ---: |
| Vavuniya    |   72 |
| Mannar      |   73 |
| Mullaitivu  |   73 |
| Kilinochchi |   73 |
| Jaffna      |   73 |

## 🛠️ Technologies

* HTML5
* CSS3
* JavaScript
* PHP 8+
* MySQL
* XAMPP
* Responsive Web Design

## 📂 Project Structure

```text
jm-paddy/
│
├── index.php
├── db.php
├── dashboard.php
├── bill.php
├── save_bill.php
├── history.php
├── view_bill.php
├── test_db.php
├── style.css
└── script.js
```

## 💻 Installation

### 1. Install XAMPP

Install XAMPP with:

* Apache
* MySQL
* phpMyAdmin

### 2. Copy Project

Copy the project folder to:

```text
C:\xampp\htdocs\jm-paddy
```

### 3. Start XAMPP

Start:

```text
Apache
MySQL
```

### 4. Create Database

Open:

```text
http://localhost/phpmyadmin
```

Create a database named:

```text
jm_paddy
```

Then create the required tables.

### 5. Configure Database

The database connection is configured in:

```text
db.php
```

Default XAMPP configuration:

```text
Host: localhost
Username: root
Password: empty
Database: jm_paddy
```

### 6. Run the System

Open:

```text
http://localhost/jm-paddy/
```

or:

```text
http://localhost/jm-paddy/dashboard.php
```

## 📱 Responsive Design

JM PADDY is designed to work on:

* 💻 Windows PC/Laptop
* 📱 Android
* 🍎 iPhone/iPad
* 🖥️ Desktop
* 🌐 Chrome
* 🌐 Edge
* 🌐 Firefox
* 🌐 Safari

## 🔄 System Workflow

```text
Login
  ↓
Dashboard
  ↓
Create Bill
  ↓
Enter Farmer Details
  ↓
Select District
  ↓
Enter Paddy Type
  ↓
Enter Today's Price
  ↓
Enter Paddy Weights
  ↓
Automatic Calculation
  ↓
Save Bill
  ↓
View Bill
  ↓
Print / PDF
```

## 🎯 Project Objective

The main objective of JM PADDY is to replace manual paddy billing processes with a simple digital system that improves:

* Accuracy
* Calculation speed
* Farmer record management
* Bill management
* Data storage
* Accessibility
* Reporting

## 🔮 Future Improvements

* Online deployment
* User authentication and roles
* Farmer CRUD management
* Advanced reports
* Monthly/yearly sales reports
* PDF download
* WhatsApp bill sharing
* Cloud database
* Backup and restore
* Mobile-friendly improvements
* Admin panel

## 👨‍💻 Developer

**M. J. M. FARIS**

BSc Information Technology Undergraduate
University of Vavuniya

GitHub: `github.com/farisjm`

## 📌 Project Status

**Development in Progress 🚧**

JM PADDY is being developed as an academic/portfolio web application.

---

### 🌾 JM PADDY

**Simple • Fast • Accurate**
