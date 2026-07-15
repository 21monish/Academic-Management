# Deployment Checklist

Use this checklist before putting GTU ITR on a shared server or production host.

## Sevalla App Hosting

Use GitHub deployment from:

```text
https://github.com/21monish/Academic-Management.git
```

Recommended Sevalla settings:

- Runtime: PHP `8.2` or newer
- Node.js: `20` or newer
- Branch: `main`
- Document root / public directory: `public`
- Start command:

```bash
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
```

The repository also includes a `Procfile` with the same start command for platforms that read it automatically.

Build command:

```bash
composer install --no-dev --optimize-autoloader --no-interaction && npm ci && npm run build && composer run app:cache
```

Post-deploy command:

```bash
composer run app:post-deploy
```

If Sevalla separates build and release commands, put `composer run app:post-deploy` in the release/post-deploy step. If it does not, run it manually from Sevalla's terminal after the first deploy and after future database changes.

Copy `.env.sevalla.example` into Sevalla environment variables and fill in:

- `APP_KEY`
- `APP_URL`
- `ASSET_URL`
- `APP_FORCE_HTTPS=true`
- `TRUSTED_PROXIES=*`
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- mail settings, if real emails are needed

Generate an app key locally if needed:

```bash
php artisan key:generate --show
```

For uploaded logos, photos, notices, and generated PDFs, configure persistent storage for:

- `public/uploads`
- `storage/app`
- `storage/logs`

Without persistent storage, uploaded files may disappear after a redeploy depending on the hosting filesystem.

## Environment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_KEY`.
- Point `APP_URL` and `ASSET_URL` to the HTTPS domain, for example `https://academic-management-uotbt.sevalla.app`.
- Set `APP_FORCE_HTTPS=true` and `TRUSTED_PROXIES=*` so Laravel redirects `http` requests and trusts Sevalla's HTTPS proxy headers.
- Use production database credentials and a non-root database user.
- Configure mail, queue, cache, and session drivers for the hosting environment.

## Build And Cache

Run these after every deploy:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan app:readiness-check
```

## Writable Paths

The web/PHP user must be able to write:

- `storage/`
- `bootstrap/cache/`
- `public/uploads/photos/`
- `public/uploads/notices/`
- `storage/app/uploads/documents/`
- `storage/app/generated/halltickets/`
- `storage/app/generated/receipts/`
- `storage/app/generated/results/`

## Security Notes

- Browser security headers are applied by `SecurityHeaders`.
- Always open the app with `https://...`; plain `http://...` requests are redirected when `APP_FORCE_HTTPS=true`.
- Backend module access is enforced by `permission:` middleware, not only by navigation visibility.
- Student users are limited to dashboard/profile/notices by default. Keep self-service data on scoped dashboard pages unless dedicated row-level routes are added.
- Do not expose `.env`, `storage/`, `vendor/`, or project root files from the web server. Only `public/` should be web-facing.
- Keep `APP_DEBUG=false` in Sevalla production.
- Do not commit `.env`, database dumps, hosting zip files, or uploaded user files.
