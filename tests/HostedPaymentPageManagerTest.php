<?php

namespace Genome\Merchant\Tests;

use Genome\Merchant\HostedPayment;
use Genome\Merchant\HostedPaymentPageManager;
use Genome\Merchant\MerchantAccountManager;
use PHPUnit\Framework\TestCase;

class HostedPaymentPageManagerTest extends TestCase {
    public function testGenerateInitializationSignature_MODE_A() {
        $merchant = new MerchantAccountManager(1, 'foo');
        $hpp = $merchant->getHostedPaymentPageManager('foo', '02ruy9h8sdygfsi766', HostedPaymentPageManager::SIGNATURE_MODE_A);

        $payment = new HostedPayment(
            "order-1",
            "user-2",
            "mcc-3",
            "uSd",
            9.99
        );

        $this->assertSame(
            '61eb9dd0e03eb2c3745dbba5dee8a4adac7b95841985cd510bdb049237bfdf3c',
            $hpp->generateInitializationSignature($payment)
        );
    }

    public function testGenerateInitializationRedirectUrl_MODE_A() {
        $merchant = new MerchantAccountManager(1, 'foo');
        $hpp = $merchant->getHostedPaymentPageManager('foo', '02ruy9h8sdygfsi766', HostedPaymentPageManager::SIGNATURE_MODE_A);

        $payment = new HostedPayment(
            "order-1",
            "user-2",
            "mcc-3",
            "uSd",
            9.99
        );

        $this->assertSame(
            'https://pay.genome.eu/?order_id=order-1&user_id=user-2&amount=9.99&currency_iso=USD&mcc=mcc-3&api_key=foo&signature=61eb9dd0e03eb2c3745dbba5dee8a4adac7b95841985cd510bdb049237bfdf3c',
            $hpp->generateInitializationRedirectUrl($payment)
        );
    }

    public function testGenerateInitializationSignature_MODE_A_TS() {
        $merchant = new MerchantAccountManager(1, 'foo');
        $hpp = $merchant->getHostedPaymentPageManager('foo', '02ruy9h8sdygfsi766', HostedPaymentPageManager::SIGNATURE_MODE_A_TS);

        $payment = new HostedPayment(
            "order-1",
            "user-2",
            "mcc-3",
            "uSd",
            9.99
        );
        $payment->setTsNonce(1761290409);

        $this->assertSame(
            '14b363ab042953068b7c6791120ea89a39653fc61744ae7e25441e9bb2b69bf8',
            $hpp->generateInitializationSignature($payment)
        );
    }

    public function testGenerateInitializationRedirectUrl_MODE_A_TS() {
        $merchant = new MerchantAccountManager(1, 'foo');
        $hpp = $merchant->getHostedPaymentPageManager('foo', '02ruy9h8sdygfsi766', HostedPaymentPageManager::SIGNATURE_MODE_A_TS);

        $payment = new HostedPayment(
            "order-1",
            "user-2",
            "mcc-3",
            "uSd",
            9.99
        );

        $payment->setTsNonce(1761290409);

        $this->assertSame(
            'https://pay.genome.eu/?order_id=order-1&user_id=user-2&amount=9.99&currency_iso=USD&mcc=mcc-3&ts_nonce=1761290409&api_key=foo&signature=14b363ab042953068b7c6791120ea89a39653fc61744ae7e25441e9bb2b69bf8',
            $hpp->generateInitializationRedirectUrl($payment)
        );
    }

    public function testAssertCallbackSignature() {
        $merchant = new MerchantAccountManager(1, 'foo');
        $hpp = $merchant->getHostedPaymentPageManager('foo','ksadasu8h89ha');

        $hpp->assertCallbackSignature(
            [
                'X-Signature-Algorithm' => 'hmacsha256',
                'X-Signature' => 'a381cf9c06fe0adf155738cfff3186b067b35ed47544a76fe38fae0c8ef5a099',
            ],
            'Hello, world!'
        );
        $this->assertTrue(true);
    }
}