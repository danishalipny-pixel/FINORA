# FINORA Installation & Deployment Guide

This guide provides step-by-step instructions to set up, configure, and run **FINORA – Personal Finance Intelligence** across different development and production environments.

---

## 1. System Requirements

Ensure your host meets the following prerequisites:

| Requirement | Minimum Version | Recommended | Notes |
|---|---|---|---|
| **PHP** | 8.2.0 | 8.3+ | CLI and Web SAPI |
| **MySQL / MariaDB** | MySQL 8.0+ / MariaDB 10.4+ | MySQL 8.0.35+ | Requires InnoDB & utf8mb4 |
| **PHP Extensions** | `pdo`, `pdo_mysql`, `mbstring`, `json`, `curl` | All enabled | Standard in modern PHP |
| **Web Server** | Built-in CLI Server | Nginx or Apache 2.4 | With `mod_rewrite` enabled |
| **Composer** | 2.5+ | Latest 2.x | For dependency management |

---

## 2. Quickstart (Development Server)

To get up and running in under 2 minutes using PHP's built-in server:

### Step 1: Clone Repository
```bash
git clone https://github.com/danishalipny-pixel/FINORA.git
cd FINORA
```

### Step 2: Configure Environment Variables
Copy the template configuration file:
```bash
cp .env.example .env
```
Open `.env` in your editor and adjust your MySQL connection parameters:
```ini
APP_NAME=FINORA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8080

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finora_db
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

### Step 3: Initialize Database & Run Migrations
Ensure MySQL is running and create the empty database:
```sql
CREATE DATABASE finora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Execute the automated migration engine:
```bash
php database/migrations/migrate.php
```

### Step 4: Seed Demo Data
Populate realistic demo transactions, accounts, budgets, and savings goals:
```bash
php database/seeders/DatabaseSeeder.php
```

### Step 5: Start Development Server
```bash
php -S 127.0.0.1:8080 -t public public/router.php
```
Open your browser at **`http://127.0.0.1:8080`**.

### Demo Credentials
- **Email:** `demo@finora.test`
- **Password:** `FinoraDemo123!`

---

## 3. Production Deployment

### 3.1. Apache Configuration
Ensure `mod_rewrite` is enabled. Set your Apache VirtualHost `DocumentRoot` strictly to the `public/` folder:

```apache
<VirtualHost *:80>
    ServerName finora.yourdomain.com
    DocumentRoot "/var/www/finora/public"

    <Directory "/var/www/finora/public">
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/finora_error.log
    CustomLog ${APACHE_LOG_DIR}/finora_access.log combined
</VirtualHost>
```

### 3.2. Nginx Configuration
Forward all requests to `public/index.php`:

```nginx
server {
    listen 80;
    server_name finora.yourdomain.com;
    root /var/www/finora/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 3.3. File & Directory Permissions
Ensure the web server process has write access to `storage/`:
```bash
chmod -R 775 storage/
chmod -R 775 storage/logs/ storage/cache/ storage/uploads/
chown -R www-data:www-data storage/
```

---

## 4. Verification & Testing

Execute the automated test suite to ensure 100% system readiness:
```bash
php tests/run_tests.php
```

---

## 5. Troubleshooting & FAQ

### Issue: "Database Connection Failed"
- Verify MySQL is active and listening on port `3306`.
- Double-check your `.env` host (`127.0.0.1` vs `localhost`).
- Confirm user permissions with:
  ```sql
  GRANT ALL PRIVILEGES ON finora_db.* TO 'root'@'127.0.0.1';
  FLUSH PRIVILEGES;
  ```

### Issue: "Storage directory is not writable"
- On Linux/macOS, execute `chmod -R 775 storage/`.
- Ensure `storage/logs/` and `storage/cache/` exist (both include `.gitkeep` files).

### Issue: "404 Not Found on Sub-pages"
- Ensure URL rewrites are active.
- When using Apache, ensure `AllowOverride All` is configured.
- When using PHP built-in server, pass `public/router.php` as the server script argument.
