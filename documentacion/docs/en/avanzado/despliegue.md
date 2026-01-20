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

Other folders (`app`, `system`, `public`) should be read-only for web server for security, unless specific needs.

## 3. Database

Update connection credentials in `.env` pointing to production database.

Ensure `app.baseURL` configured correctly with real domain (HTTPS recommended).

```ini
app.baseURL = 'https://my-production-system.com/'
```

## 4. Remove Development Files

Recommended not to upload unnecessary files to production:

- Tests (`tests/`)
- Git files (`.git/`, `.gitignore`)
- Internal documentation (`documentacion/`)
- SQL sample files (`sampledatabase/`)

## Common Troubleshooting

**404 Error on all pages except Home:**
Usually web server config (Apache/Nginx) not redirecting requests to `index.php`.
Ensure `public/.htaccess` present and `mod_rewrite` active in Apache.

**Blank Page or 500 Error:**
Check logs in `writable/logs/` for real error, as production mode hides them.
