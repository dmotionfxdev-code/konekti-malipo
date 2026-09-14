# Testing

Run `php artisan test`. Use `Http::fake()` for provider tests; never contact a live gateway. Cover status mapping, duplicate callbacks, malformed payloads, unsupported currencies, and initialization failures.
