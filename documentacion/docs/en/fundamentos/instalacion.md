# Installation of Ragnos

This guide describes how to install Ragnos Framework from a distributed ZIP file.

## Server Requirements

Ragnos is based on **CodeIgniter 4**, so it shares its minimum requirements:

- **PHP**: Version 7.4 or higher (PHP 8.1+ recommended).
- **PHP Extensions**:
  - intl
  - mbstring
  - json
  - mysqlnd (if using MySQL/MariaDB)
  - curl
  - gd (optional, for image manipulation)
- **Database**: MySQL (5.1+) or MariaDB.

## Installation Steps

### 1. Download and Extraction

1. Download the `.zip` file with the latest version of Ragnos.
2. Extract the content into your web server directory (e.g., `c:\laragon\www\my-project` or `/var/www/html/my-project`).
3. Verify that the `vendor` folder exists and contains dependencies. Ragnos already includes all necessary libraries.

### 2. Environment Configuration

1. Locate the `env` file in the project root.
2. Rename or copy it to `.env`.
3. Open the `.env` file and adjust the following variables:

!!! note "Watch the dot"

    Make sure the file is named exactly `.env` (with the dot at the beginning) and not just `env`.

**Environment:**

```ini
CI_ENVIRONMENT = development
```

**Base URL:**

```ini
app.baseURL = 'http://localhost/my-project/'
```

**Database:**
Uncomment and configure your database credentials:

```ini
database.default.hostname = localhost
database.default.database = your_db_name
database.default.username = your_user
database.default.password = your_password
database.default.DBDriver = MySQLi
```

### 3. Import Database

Ragnos requires certain base tables to function (users, sessions, permissions).

1. Create an empty database in your manager (phpMyAdmin, HeidiSQL, etc.).
2. Import the SQL files located in the `sampledatabase/` folder:
   - Run `ragnos_mariadb.sql` first (or the main dump containing the base structure).
   - Run `ci_sessions.sql` for the sessions table.
   - (Optional) Run `mysqlsampledatabase.sql` if you want to load the **Classicmodels** sample data. See [Demo Database](demo_database.md) for more details.

### 4. Verify Permissions

Ensure that the `writable/` folder and its subfolders have write permissions by the web server.

### 5. Run

Access your browser at the configured URL (e.g., `http://localhost/my-project/content` or simply `http://my-project.test` if using Laragon).

!!! note "Public folder change"

    In the source code, the `public` folder has been renamed to `content`. However, it is fine to rename it to whatever suits your server best.

You should see the login screen.

### 6. Access and Demo Data

To obtain default access credentials and learn more about the included sample data (**Classicmodels**), please refer to the [Demo Database](demo_database.md) section.
