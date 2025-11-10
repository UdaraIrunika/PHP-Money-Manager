# Money Manager

A personal finance management web application built with PHP and MySQL. Track your income, expenses, budgets, and financial goals with an intuitive dashboard and reporting features.

## Repository

🔗 **GitHub**: [https://github.com/UdaraIrunika/PHP-Money-Manager.git](https://github.com/UdaraIrunika/PHP-Money-Manager.git)

## Features

- 💰 **Transaction Management** - Track income and expenses with categories
- 📊 **Budget Tracking** - Set and monitor monthly budgets
- 🎯 **Financial Goals** - Create and track savings goals
- 📈 **Reports & Analytics** - Visualize spending patterns with Chart.js
- 📤 **Data Export** - Export transactions to CSV
- 🔐 **Secure Authentication** - Password hashing and user sessions

## Technologies

- **Backend**: PHP with PDO (prepared statements)
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Charts**: Chart.js (CDN)
- **Server**: XAMPP (Apache + MySQL)

## Installation

### Prerequisites

- XAMPP (or any Apache + MySQL + PHP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone or download** this repository into your XAMPP `htdocs` folder:
   ```
   d:/XAMPP/htdocs/ALL_PROJECTS/money_manager/
   ```

2. **Create a MySQL database**:
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create a new database named `money_manager`

3. **Import the database schema**:
   - In phpMyAdmin, select the `money_manager` database
   - Click on the "Import" tab
   - Choose the file `sql/schema.sql` from this project
   - Click "Go" to import

4. **Configure database connection**:
   - Open `config.php` in a text editor
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'money_manager');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

5. **Start XAMPP services**:
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

6. **Access the application**:
   - Open your browser and navigate to:
     ```
     http://localhost/ALL_PROJECTS/money_manager/index.php
     ```

## Project Structure

```
money_manager/
├── assets/
│   ├── css/
│   │   └── style.css           # Application styles
│   └── js/
│       └── app.js              # Client-side JavaScript
├── sql/
│   └── schema.sql              # Database schema and sample data
├── config.php                  # Database configuration
├── functions.php               # Helper functions and middleware
├── index.php                   # Login page
├── register.php                # User registration
├── logout.php                  # Logout handler
├── dashboard.php               # Main dashboard
├── transactions.php            # Transaction management
├── categories.php              # Category management
├── budgets.php                 # Budget tracking
├── goals.php                   # Financial goals
├── reports.php                 # Reports and analytics
└── export_csv.php              # CSV export functionality
```

## Security Features

- ✅ Password hashing using PHP's `password_hash()`
- ✅ Prepared statements with PDO to prevent SQL injection
- ✅ Session-based authentication
- ✅ Input validation and sanitization

## Usage

1. **Register** a new account on the registration page
2. **Login** with your credentials
3. **Add transactions** to track your income and expenses
4. **Create categories** to organize your transactions
5. **Set budgets** for different spending categories
6. **Define goals** to save for specific targets
7. **View reports** to analyze your spending patterns
8. **Export data** to CSV for external analysis

## Database Schema

The application uses the following main tables:
- `users` - User accounts and authentication
- `transactions` - Income and expense records
- `categories` - Transaction categories
- `budgets` - Monthly budget limits
- `goals` - Financial savings goals

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues or questions, please open an issue on the GitHub repository.

---

**Note**: This is a development project using XAMPP. For production deployment, consider additional security measures and a proper hosting environment.
