# REMS - Real Estate Management System

A complete Real Estate Management System built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

- **Dashboard**: Overview of properties, units, tenants, and revenue with interactive charts
- **Properties Management**: Add, edit, and delete properties with occupancy tracking
- **Units Management**: Manage individual units within properties
- **Tenant Management**: Track tenant information and lease details
- **Lease Management**: Create and manage lease agreements
- **Payment Tracking**: Record and track rent payments
- **Maintenance Requests**: Submit and track maintenance issues
- **Reports**: View analytics and generate reports

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

### 1. Copy Files

Copy all files from the `rems` folder to your web server's root directory or a subdirectory.

### 2. Create Database

1. Create a new MySQL database
2. Import the `database.sql` file located in the project root

```bash
mysql -u root -p < database.sql
```

### 3. Configure Database Connection

Edit `includes/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'rems_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Set Permissions

Make sure the uploads directory is writable:

```bash
chmod -R 755 assets/uploads
```

### 5. Access the Application

Open your browser and navigate to the application URL.

## Default Login Credentials

- **Email**: admin@rems.com
- **Password**: password

## Directory Structure

```
rems/
├── index.php              # Login page
├── dashboard.php          # Main dashboard
├── logout.php             # Logout handler
├── includes/
│   ├── config.php         # Configuration and session start
│   ├── db.php             # Database connection class
│   ├── functions.php      # Helper functions
│   ├── auth.php           # Authentication functions
│   ├── header.php         # Common header template
│   └── footer.php         # Common footer template
├── auth/
│   └── register.php       # User registration
├── modules/
│   ├── properties/        # Properties management
│   ├── units/             # Units management
│   ├── tenants/           # Tenants management
│   ├── leases/            # Leases management
│   ├── payments/          # Payments tracking
│   ├── maintenance/       # Maintenance requests
│   └── reports/           # Reports and analytics
└── assets/
    ├── css/
    │   └── style.css      # Custom styles
    ├── js/
    │   └── main.js        # Custom JavaScript
    └── uploads/           # Uploaded files
        ├── properties/
        ├── leases/
        ├── documents/
        └── profiles/
```

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Tailwind CSS (via CDN)
- **Icons**: Font Awesome (via CDN)
- **Charts**: Chart.js (via CDN)

## Security Features

- PDO prepared statements for SQL injection prevention
- Password hashing using `password_hash()`
- Session-based authentication
- Input sanitization
- CSRF protection (can be added)

## License

This project is open-source and available under the MIT License.

## Support

For issues and feature requests, please create an issue in the repository.
