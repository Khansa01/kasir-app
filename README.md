# 🧾 KasirKu — Point of Sale System

A modern, lightweight Point of Sale (POS) web application built with pure PHP and MySQL. Designed for small businesses and retail stores.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat)

---

## ✨ Features

- 🔐 **Multi-role Authentication** — Admin and Cashier roles with session-based auth
- 🛒 **Real-time Transaction** — Interactive POS interface with live cart updates
- 📦 **Product Management** — Full CRUD with category support and stock tracking
- 📈 **Sales Reports** — Filter transactions by date range with revenue summary
- 🖨️ **Receipt Printing** — Print-ready receipt for every transaction
- 👥 **User Management** — Admin can manage cashier accounts
- 💳 **Multiple Payment Methods** — Cash, Transfer, QRIS support
- 📱 **Collapsible Sidebar** — Optimized for tablet use

---

## 🛠️ Tech Stack

| Layer        | Technology                             |
| ------------ | -------------------------------------- |
| Backend      | PHP 8.2 (Pure PHP, no framework)       |
| Database     | MySQL 8.0                              |
| Frontend     | HTML5, CSS3, Vanilla JavaScript        |
| Architecture | MVC-like (Models separated from Views) |
| Auth         | PHP Sessions                           |
| DB Access    | PDO with prepared statements           |

---

## 📁 Project Structure

kasir-app/
├── auth/
│ ├── login.php
│ └── logout.php
├── config/
│ └── db.php # Database configuration
├── includes/
│ ├── header.php # Shared header + sidebar
│ └── footer.php
├── models/
│ ├── UserModel.php # User queries
│ ├── ProdukModel.php # Product queries
│ ├── TransaksiModel.php # Transaction queries
│ └── KategoriModel.php # Category queries
├── pages/
│ ├── dashboard.php # Sales overview
│ ├── transaksi.php # POS interface
│ ├── produk.php # Product management
│ ├── laporan.php # Sales reports
│ ├── pengguna.php # User management
│ └── struk.php # Receipt view
├── assets/
│ └── style.css # Global styles
├── database.sql # Database schema + seed data
├── Dockerfile
└── index.php

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Apache (XAMPP / Laragon recommended for local)

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/Khansa01/kasir-app.git
cd kasir-app
```

**2. Import the database**

```bash
mysql -u root -p < database.sql
```

Or import via phpMyAdmin / MySQL Workbench.

**3. Configure database connection**

Copy and edit the config file:

```bash
cp config/db.example.php config/db.php
```

Edit `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'kasir_db');
```

**4. Run with XAMPP**

Place the project in `htdocs/` and open:

http://localhost/kasir-app/

---

## 👤 Default Credentials

| Role    | Username | Password   |
| ------- | -------- | ---------- |
| Admin   | `admin`  | `password` |
| Cashier | `kasir`  | `password` |

> ⚠️ Change default passwords after first login!

---

## 📸 Screenshots

### Dashboard

> Sales overview with today's stats and weekly chart

### POS Interface

> Real-time cart with product grid, payment methods, and receipt modal

### Sales Report

> Date-range filter with transaction history and revenue summary

---

## 🔒 Security

- Passwords hashed with `password_hash()` (bcrypt)
- All inputs sanitized with `htmlspecialchars()` and `strip_tags()`
- Database queries use PDO prepared statements (SQL injection prevention)
- Config directory protected with `.htaccess`

---

## 📄 License

This project is licensed under the MIT License.

---

## 👩‍💻 Author

**Khansa Kamila**  
[GitHub](https://github.com/Khansa01)
