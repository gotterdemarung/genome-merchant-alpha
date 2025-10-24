Genome Merchant Client Library
==============================

![PHP](https://img.shields.io/badge/PHP-7.2+-blue)
![CI](https://github.com/gotterdemarung/genome-merchant-alpha/actions/workflows/tests.yml/badge.svg)

Genome merchant account client library.

## Easy start

Initialize merchant account manager using merchant account identifier and secret.

```php
use Genome\Merchant\MerchantAccountManager;

$merchantAccountId = 1;         // Your merchant account identifier
$merchantAccountSecret = "foo"; // Your merchant account secret

$accountManager = new MerchantAccountManager($merchantAccountId, $merchantAccountSecret);
```

Then obtain hosted payment page manager from merchant account manager
by providing hosted payment page api key and secret (they differ from merchant account ones).

```php

$hppApiKey = 'xxx';
$hppApiSecret = 'yyy';

$hppManager = $accountManager->getHostedPaymentPageManager($hppApiKey, $hppApiSecret)
```

Verify hosted payment page callback signature

```php

try {
    $hppManager->assertCallbackSignature(getallheaders(), file_get_contents('php://input'));
} catch (\Exception e) {
    // Signature assertion failed
}
```

Or initialize new payment redirect using `HostedPayment`:

```php
$payment = new HostedPayment(
    "uniqueOrderId",
    "userId",
    "mcc",
    "eur",
    9.99
);

$redirectUrl = $hppManager->generateInitializationRedirectUrlMODE_A($payment); 
```

The `HostedPayment` is a mutable object containing payment data, it provides
setters for additional parameters like email, phone, etc.