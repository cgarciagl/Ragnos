# Deployment Guide (Production)

This guide covers the essential steps to take your Ragnos application from local development to a production server.

## 1. Environment Configuration

!!! danger "Important"

    In production, **never** run the application in `development` mode. It exposes sensitive information and slows the application down.

1. Edit the `.env` file on the server.
2. Set the environment variable:

```ini
CI_ENVIRONMENT = production
```

This disables the debug toolbar, hides detailed PHP errors, and enables configuration caching.

## 2. File System Permissions

Make sure the web server user (e.g. `www-data` on Apache/Nginx) has **write** permission on the `writable` folder and its subfolders.

```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

For security, the other folders (`app`, `src`, `vendor`, and the public folder `public`) should be read-only for the web server, unless you have specific needs.

## 3. Database

Update the connection credentials in `.env` so they point to the production database.

Make sure `app.baseURL` is set correctly to your real domain (HTTPS is recommended).

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

Point the server's *Document Root* to the Ragnos public folder (`public`). If your hosting requires another name (`content`, `public_html`, `www`), rename it and update `$publicDirectory` in `app/Config/Paths.php`; see [Configuration](../fundamentos/configuracion.md#public-folder-public).

For extra security you can disable Auto Routing (`$autoRoute = false`): Ragnos will register explicit routes for its actions only. See [Working Without Auto Routing](../fundamentos/configuracion.md#working-without-auto-routing-explicit-routes).

## 5. Remove Development Files

We recommend not uploading files that are unnecessary in production:

- Tests (`tests/`)
- Git files (`.git/`, `.gitignore`)
- Internal documentation (`documentacion/`)
- SQL sample files (`sampledatabase/`)

## Common Troubleshooting

**404 Error on all pages except Home:**
This is usually because the web server (Apache/Nginx) is not routing requests to `index.php`.
Make sure the public folder's `.htaccess` file (`public/.htaccess`) is present and that `mod_rewrite` is enabled in Apache.

**Blank Page or 500 Error:**
Check the logs in `writable/logs/` for the real error, since production mode hides it.
