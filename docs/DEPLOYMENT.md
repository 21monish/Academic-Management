# Deployment Checklist

Use this checklist before putting GTU ITR on a shared server or production host.

## Environment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_KEY`.
- Point `APP_URL` to the HTTPS domain.
- Use production database credentials and a non-root database user.
- Configure mail, queue, cache, and session drivers for the hosting environment.

## Build And Cache

Run these after every deploy:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
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
- Backend module access is enforced by `permission:` middleware, not only by navigation visibility.
- Student users are limited to dashboard/profile/notices by default. Keep self-service data on scoped dashboard pages unless dedicated row-level routes are added.
- Do not expose `.env`, `storage/`, `vendor/`, or project root files from the web server. Only `public/` should be web-facing.
