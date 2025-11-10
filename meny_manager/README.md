Money Manager - PHP/MySQL

Setup

1. Place this folder into your XAMPP htdocs (already in d:/XAMPP/htdocs/ALL_PROJECTS/meny_manager).
2. Create a MySQL database, e.g., `money_manager`.
3. Import the SQL file `sql/schema.sql` into the database (use phpMyAdmin or mysql client).
4. Edit `config.php` and set DB credentials.
5. Start Apache + MySQL in XAMPP and open http://localhost/ALL_PROJECTS/meny_manager/index.php

Notes
- Uses PHP PDO with prepared statements.
- Passwords hashed with password_hash().
- Chart.js loaded from CDN for charts.

Files
- `config.php` - DB connection and settings
- `functions.php` - helper functions and auth middleware
- `index.php`, `register.php`, `logout.php`, `dashboard.php`, `transactions.php`, `categories.php`, `budgets.php`, `goals.php`, `reports.php`, `export_csv.php`
- `assets/css/style.css`, `assets/js/app.js`
- `sql/schema.sql` - schema + sample data

