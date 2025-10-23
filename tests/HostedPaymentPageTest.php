<?php

use Genome\Merchant\MerchantAccount;
use PHPUnit\Framework\TestCase;

class HostedPaymentPageTest extends TestCase {
    public function testGenerateInitializationSignatureMODE_A() {
        $merchant = new MerchantAccount(1, "foo");
        $hpp = $merchant->getHostedPaymentPage("02ruy9h8sdygfsi766");

        $this->assertSame(
            "61eb9dd0e03eb2c3745dbba5dee8a4adac7b95841985cd510bdb049237bfdf3c",
            $hpp->generateInitializationSignatureMODE_A(9.99, "USD", "order-1", "user-2", "mcc-3")
        );
    }

    public function testAssertCallbackSignature() {
        $merchant = new MerchantAccount(1, "foo");
        $hpp = $merchant->getHostedPaymentPage("ksadasu8h89ha");

        $hpp->assertCallbackSignature(
            [
                "X-Signature-Algorithm" => "hmacsha256",
                "X-Signature" => "a381cf9c06fe0adf155738cfff3186b067b35ed47544a76fe38fae0c8ef5a099",
            ],
            "Hello, world!"
        );
        $this->assertTrue(true);
    }
}