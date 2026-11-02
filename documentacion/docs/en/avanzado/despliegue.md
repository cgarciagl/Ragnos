# Deployment Guide (Production)

This guide covers essential steps to take your Ragnos application from local development to production server.

## 1. Environment Configuration

!!! danger "Important"

    In production, **never** run application in `development` mode. Exposes sensitive info and slows down app.

1. Edit `.env` file on server.
2. Set environment variable:

```ini
CI_ENVIRONMENT = production
```

Disables debug toolbar, hides detailed PHP errors, activates config caching.

## 2. File System Permissions

Ensure web server user (e.g. `www-data` on Apache/Nginx) has **write** permission on `writable` folder and subfolders.

```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

Other folders (`app`, `src`, `vendor` and the public folder `content`) should be read-only for web server for security, unless specific needs.

## 3. Database

Update connection credentials in `.env` pointing to production database.

Ensure `app.baseURL` configured correctly with real domain (HTTPS recommended).

```ini
app.baseURL = 'https://my-production-system.com/'
```

### Migrations and Credentials

Run pending migrations on every deployment. Among other things, they widen the password column to use `password_hash()`:

```bash
php spark migrate
```

Before opening the system, change the `admin` password from the user menu → *Change password* (it asks for the current one and at least 8 characters).

!!! info "Passwords from earlier versions"

    If your database comes from a Ragnos version that stored md5 passwords, there's no need to reset them: each user keeps logging in with their password, and Ragnos upgrades its hash to `password_hash()` on that first login (requires having run `php spark migrate`).

!!! success "Manage the production database with PyroStudio"

    On shared hosting, the MySQL port is almost never open. [**PyroStudio**](https://github.com/cgarciagl/PyroStudio), the desktop client by the author of Ragnos, connects through an **HTTP tunnel**: upload `tunnel/pyro_tunnel.php` next to your application and manage the database over HTTPS, without opening ports. The tunnel requires a secret token and only connects to databases on `localhost`. Mark the connection as *Production* so its Safe Mode asks for confirmation before destructive statements, and use *Backup / Restore* to back up before each deployment.

## 4. Public Folder and Web Server

Point the server's *Document Root* to the Ragnos public folder (`content`). If your hosting requires another name (`public_html`, `www`), rename it and update `$publicDirectory` in `app/Config/Paths.php`; see [Configuration](../fundamentos/configuracion.md#public-folder-content).

For extra security you can disable Auto Routing (`$autoRoute = false`): Ragnos will register explicit routes for its actions only. See [Working Without Auto Routing](../fundamentos/configuracion.md#working-without-auto-routing-explicit-routes).

## 5. Remove Development Files

Recommended not to upload unnecessary files to production:

- Tests (`tests/`)
- Git files (`.git/`, `.gitignore`)
- Internal documentation (`documentacion/`)
- SQL sample files (`sampledatabase/`)

## Common Troubleshooting

**404 Error on all pages except Home:**
Usually web server config (Apache/Nginx) not redirecting requests to `index.php`.
Ensure the public folder's `.htaccess` (`content/.htaccess`) is present and `mod_rewrite` active in Apache.

**Blank Page or 500 Error:**
Check logs in `writable/logs/` for real error, as production mode hides them.
