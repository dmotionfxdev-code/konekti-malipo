# cPanel deployment

1. Extract `konekti-malipo-cpanel.zip` outside `public_html`, for example at `/home/CPANEL_USER/konekti-malipo`.
2. Point the domain document root to `/home/CPANEL_USER/konekti-malipo/public`. If the host cannot change the document root, copy the contents of `public/` to `public_html` and update `public_html/index.php` paths to the application directory.
3. Create a MySQL database and database user in cPanel. Configure `DB_*`, `APP_URL=https://konektimalipo.deeteki.com`, `APP_ENV=production`, and a fresh `APP_KEY` in `.env` (copy from `.env.example`).
4. The archive intentionally does not contain `vendor/`; in cPanel Terminal run `composer install --no-dev --optimize-autoloader`, then run `php artisan optimize:clear`, `php artisan key:generate --force`, `php artisan migrate --force`, `php artisan config:cache`, and `php artisan route:cache`.
5. Ensure `storage/` and `bootstrap/cache/` are writable by the web server. Configure this cPanel cron job every minute: `php /home/CPANEL_USER/konekti-malipo/artisan schedule:run`. It runs pending-payment reconciliation every five minutes: the app verifies Pesapal first and cancels/marks still-pending checkouts as expired after `PAYMENT_PENDING_EXPIRY_MINUTES` (default 30).
6. Sign in, save each user's Pesapal credentials in the dashboard, and let Konekti Malipo register the account-specific IPN URL automatically.

Do not upload a local `.env`, `database/database.sqlite`, test outputs, or local cache/log files. The archive intentionally excludes them.
