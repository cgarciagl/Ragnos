# Installation of Ragnos

This guide describes how to install Ragnos Framework. You can choose between two methods: the **direct download of the ready-to-run standalone ZIP package** (no Composer or Git required) or installation via **Git and Composer** (recommended for active development).

## Server Requirements

Ragnos is based on **CodeIgniter 4**, so it shares its minimum requirements:

- **PHP**: Version 8.1 or higher.
- **Composer 2.x** (only if installing via Git or planning to manage packages manually).
- **Required PHP Extensions**:
  - intl
  - mbstring
  - json (always included in PHP 8)
  - mysqli (the `MySQLi` driver CodeIgniter uses to connect to MySQL and MariaDB)
- **Optional PHP Extensions**:
  - curl (only if you call external services through CodeIgniter's `CURLRequest` class)
  - fileinfo (recommended if you use file upload fields: it improves MIME type detection)
  - gd (only if you use CodeIgniter's `Image` class to manipulate images)
- **Database**: MariaDB 10.2.1 or later, or MySQL 8.0 or later. Regular datasets work with earlier versions, but `RQueryController` uses common table expressions (`WITH`), which those minimum versions were the first to support.

## Installation Steps

### 1. Get the Code

Choose the option that best fits your needs and work environment:

#### Option A: Standalone ZIP Package (Quick installation without Composer)

Ideal for shared hosting (cPanel, Plesk), quick testing environments, or if you prefer not to use the terminal or Composer:

1. Download the pre-packaged [ragnos.zip](https://github.com/cgarciagl/Ragnos/releases/latest/download/ragnos.zip) from the latest release on GitHub Releases.
2. Extract its contents directly into your web server directory (e.g., `c:\laragon\www\my-project` or `/var/www/html/my-project`).

!!! success "All-in-one"
    This ZIP archive already bundles the Ragnos core, CodeIgniter 4, and all required dependencies inside the `vendor/` folder. You do not need to run `composer install`.

#### Option B: Via Git and Composer (Recommended for development)

Recommended if you work with version control and want to manage and update dependencies with `composer update`:

1. Clone the repository into your web server directory:

    ```bash
    git clone https://github.com/cgarciagl/Ragnos.git my-project
    cd my-project
    ```

2. Install dependencies (including CodeIgniter 4) with Composer:

    ```bash
    composer install
    ```

    This step creates the `vendor/` folder. CodeIgniter is installed as a dependency, so you receive security patches with a simple `composer update`.

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

### 3. Create the Database

Ragnos splits its tables into two scripts inside `sampledatabase/`:

| Script | Contents | Required? |
| :----- | :------- | :-------: |
| `ragnos_base.sql` | System tables: users, groups, audit log and sessions. Includes the `admin` user. | ✅ |
| `demo_classicmodels.sql` | Scale-model store demo (**Classicmodels**): catalogs, reports and sales dashboard. | Optional |

1. Create an empty database with the same name you set in `database.default.database` (`ragnos` by default):

    ```sql
    CREATE DATABASE ragnos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

2. Import the system tables:

    ```bash
    mysql -u root -p ragnos < sampledatabase/ragnos_base.sql
    ```

3. (Optional) Import the demo to explore Ragnos with real data. See [Demo Database](demo_database.md) for its structure.

    ```bash
    mysql -u root -p ragnos < sampledatabase/demo_classicmodels.sql
    ```

!!! success "Recommended: PyroStudio, by the author of Ragnos"

    [**PyroStudio**](https://github.com/cgarciagl/PyroStudio) is the desktop client for **MariaDB, MySQL and SQLite** created by the author of Ragnos, designed to work hand in hand with the framework. It's free (MIT), native and ultralight (Tauri + Rust, no Electron or Java) and runs on Windows, macOS and Linux.

    To load the scripts: right-click the `ragnos` database → *Backup / Restore* → *Restore* tab → *Restore into Existing DB*, and choose `ragnos_base.sql` first, then `demo_classicmodels.sql`.

    It also includes a **PHP HTTP tunnel** to manage the database of a shared hosting that doesn't expose the MySQL port (exactly where Ragnos is often deployed), **Safe Mode** that asks for confirmation before destructive statements, and encrypted credentials.

    Download it from [Releases](https://github.com/cgarciagl/PyroStudio/releases).

!!! tip "Using HeidiSQL or phpMyAdmin?"

    Select the database and use *File → Load SQL file* (HeidiSQL) or the *Import* tab (phpMyAdmin). The scripts don't hard-code the database name, so they import into whichever one is selected.

!!! tip "Using Laragon on Windows?"

    Open the terminal from Laragon's *Terminal* button: it already has `mysql` on the PATH (the `root` user has no password; you can omit `-p`).

!!! warning "Installation scripts"

    Both scripts drop and recreate their tables. Use them to install, never on a database with data you want to keep.

!!! info "With or without the demo"

    Without the demo, Ragnos starts with a system home panel (users, groups and recent activity) and the menu only shows *Home* and *Administration*. Importing `demo_classicmodels.sql` automatically brings up the *Catalogs*, *Reports* and *Processes* menus and the store's sales dashboard. No configuration change or restart is needed: you can load the demo at any time.

### 4. Verify Permissions

Ensure that the `writable/` folder and its subfolders have write permissions by the web server.

### 5. Run

Access your browser at the configured URL (e.g., `http://localhost/my-project/public` or simply `http://my-project.test` if using Laragon).

!!! note "Public folder change"

    The public folder is called `public` by default. If your server requires another name (for example, `content` or `public_html`), you can change it; see [Public Folder](configuracion.md#public-folder-public) for the steps.

You should see the login screen.

### 6. Access and Demo Data

To obtain default access credentials and learn more about the included sample data (**Classicmodels**), please refer to the [Demo Database](demo_database.md) section.

## Migrating from Earlier Versions (`app/ThirdParty/Ragnos`)

If your project was created with an earlier Ragnos version, the core lived in `app/ThirdParty/Ragnos` under the `App\ThirdParty\Ragnos` namespace, and CodeIgniter was copied into `system/`. To upgrade:

1. Replace the `app/ThirdParty/Ragnos` folder with the new `src/` folder and delete `system/`.
2. Copy the changes from `composer.json` (the `codeigniter4/framework` dependency and the `"Ragnos\\": "src/"` autoload), `app/Config/Paths.php` and `app/Config/Autoload.php`, then run `composer install`.
3. Search and replace `App\ThirdParty\Ragnos\` with `Ragnos\` in your code.
4. Run `php spark migrate` (it widens the password column; existing md5 passwords are upgraded automatically on the next login).
5. If your `.env` was committed, remove it from the repository with `git rm --cached .env` and rotate any password that was ever pushed.

!!! info "Temporary compatibility"

    Ragnos ships an alias loader (`src/Compat/legacy_namespace.php`) so existing `use App\ThirdParty\Ragnos\...` statements keep working while you migrate. It is deprecated and will be removed in a future major version.
