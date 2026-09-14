# Pesapal API 3.0

The provider uses bearer-token authentication, a registered IPN ID, `SubmitOrderRequest`, and `GetTransactionStatus`. It redirects to Pesapal's hosted checkout. On IPN and browser callback it verifies the `OrderTrackingId`; notification data alone never establishes payment.

Sandbox: `https://cybqa.pesapal.com/pesapalv3/api`. Production: `https://pay.pesapal.com/v3/api`.

Official references: [Authentication](https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/authentication), [order submission](https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/submitorderrequest), [status verification](https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/gettransactionstatus), and [IPN registration](https://developer.pesapal.com/how-to-integrate/e-commerce/api-30-json/registeripnurl).
