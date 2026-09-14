# Adding a gateway

Implement `PaymentGatewayInterface`; create a provider-specific client and mapper; register the provider; add configuration; and add mocked tests. Remote statuses must become `PaymentStatus` values and no provider may expose its own statuses to the application.
