# Payment engine architecture

```text
Application / Facade / API -> PaymentManager -> GatewayManager -> PaymentGatewayInterface
                                                       -> PesapalProvider -> PesapalClient -> API 3.0
                                                                          -> PesapalMapper
```

`PaymentManager` owns the provider-neutral domain and persistence. A provider translates between that domain and an external API. To add a gateway, implement `PaymentGatewayInterface`, isolate HTTP/authentication in its client, normalize statuses in a mapper, register it in `PaymentServiceProvider`, add configuration, and test mocked responses.

Callbacks receive an idempotency key and are saved before a state update. Payloads are not proof of payment: the manager verifies the remote transaction before the atomic payment update.
