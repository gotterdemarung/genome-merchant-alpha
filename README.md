Genome Merchant Client Library
==============================

Initialize merchant account accessor object.

```php
use Genome\Merchant\MerchantAccountManager;

$merchantAccountId = 1;         // Your merchant account identifier
$merchantAccountSecret = "foo"; // Your merchant account secret

$merchantAccount = $merchant = new MerchantAccountManager($merchantAccountId, $merchantAccountSecret);
```


### Hosted Payment Pages

```php

$hppSecret = "foo"; // Your HPP secret

$hppManager = $merchant->assertCallbackSignature($hppSecret);
```

Verify signature of obtained callback:
```php

try {
    $hppManager->assertCallbackSignature(getallheaders(), file_get_contents('php://input'));
}

```