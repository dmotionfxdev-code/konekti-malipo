# Installation

Run `composer install`, configure a database, and run `php artisan migrate`. Add the payment variables from `.env.example`. Public callback/IPN URLs must use HTTPS and be reachable by Pesapal. Run a queue worker in production for listeners and future reconciliation work.
