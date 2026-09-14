# Webhooks

`POST|GET /api/payments/webhooks/pesapal` receives IPNs and `GET /api/payments/callback/pesapal` receives browser returns. The system records the callback, deduplicates it, verifies the tracking ID remotely, and returns Pesapal's expected JSON acknowledgement for IPN requests.

## Pending, failed, and cancelled payments

Pesapal's verified status is the source of truth: `COMPLETED` becomes `paid`, `FAILED`/`INVALID` becomes `failed`, and `REVERSED` becomes `cancelled`. A wrong PIN/password, insufficient balance, or another provider-side decline is reported by Pesapal as a verified failure through IPN/callback reconciliation; a checkout redirect alone never proves a failure or success.

The scheduled `payments:reconcile-pending` command verifies old pending payments before acting. If Pesapal still reports pending after the configured expiry window, the app calls API 3.0 `CancelOrder` and marks the local payment `expired`.
