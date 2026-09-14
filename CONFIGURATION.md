# Configuration

`config/payment.php` isolates gateway settings. Set `PESAPAL_ENVIRONMENT` to `sandbox` or `production`, use credentials from that environment only, and set `PESAPAL_IPN_ID` to the ID returned by Pesapal after registering the public IPN endpoint. Never commit credentials.
