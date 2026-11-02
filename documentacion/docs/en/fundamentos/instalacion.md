# Installation of Ragnos

This guide describes how to install Ragnos Framework by cloning the repository (or downloading the ZIP) and installing its dependencies with Composer.

## Server Requirements

Ragnos is based on **CodeIgniter 4**, so it shares its minimum requirements:

- **PHP**: Version 8.1 or higher.
- **Composer** 2.x.
- **PHP Extensions**:
  - intl
  - mbstring
  - json
  - mysqlnd (if using MySQL/MariaDB)
  - curl
  - gd (optional, for image manipulation)
- **Database**: MySQL (5.1+) or MariaDB.

## Installation Steps

### 1. Get the Code and Install Dependencies

1. Clone the repository into your web server directory (e.g., `c:\laragon\www\my-project` or `/var/www/html/my-project`):

    ```bash
    git clone https://github.com/cgarciagl/Ragnos.git my-project
    cd my-project
    ```

    If you prefer not to use Git, download the [.zip](https://github.com/cgarciagl/Ragnos/archive/refs/heads/main.zip) and extract it in the same location.

2. Install the dependencies (including CodeIgniter 4) with Composer:

    ```bash
    composer install
    ```

    This step creates the `vendor/` folder. CodeIgniter is no longer copied into the repository; it is installed as a dependency, so you get its security patches with a simple `composer update`.

!!! note "Where is the Ragnos core?"

    The framework code lives in the `src/` folder under the `Ragnos\` namespace (for example, `Ragnos\Controllers\RDatasetController`). Your application stays in `app/`.

### 2. Environment Configuration

1. Copy the `.env.example` template in the project root to `.env`:

    ```bash
    cp .env.example .env
    ```

2. Open the `.env` file and adjust the following variables:

!!! warning "Don't commit your `.env`"

    The `.env` file contains credentials and is excluded in `.gitignore`. Only version `.env.example`, without real passwords.

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

## Migrating from Earlier Versions (`app/ThirdParty/Ragnos`)

If your project was created with an earlier Ragnos version, the core lived in `app/ThirdParty/Ragnos` under the `App\ThirdParty\Ragnos` namespace, and CodeIgniter was copied into `system/`. To upgrade:

1. Replace the `app/ThirdParty/Ragnos` folder with the new `src/` folder and delete `system/`.
2. Copy the changes from `composer.json` (the `codeigniter4/framework` dependency and the `"Ragnos\\": "src/"` autoload), `app/Config/Paths.php` and `app/Config/Autoload.php`, then run `composer install`.
3. Search and replace `App\ThirdParty\Ragnos\` with `Ragnos\` in your code.
4. If your `.env` was committed, remove it from the repository with `git rm --cached .env` and rotate any password that was ever pushed.

!!! info "Temporary compatibility"

    Ragnos ships an alias loader (`src/Compat/legacy_namespace.php`) so existing `use App\ThirdParty\Ragnos\...` statements keep working while you migrate. It is deprecated and will be removed in a future major version.
