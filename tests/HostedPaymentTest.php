<?php

namespace Genome\Merchant\Tests;

use Genome\Merchant\HostedPayment;
use PHPUnit\Framework\TestCase;

class HostedPaymentTest extends TestCase
{
    public function testBuildQueryShort()
    {
        $payment = new HostedPayment(
            "12345",
            "678",
            "90",
            "usd",
            1.99
        );

        $this->assertSame(
            "order_id=12345&user_id=678&amount=1.99&currency_iso=USD&mcc=90",
            $payment->buildQuery()
        );
    }

    public function testBuildQueryFull()
    {
        $payment = new HostedPayment(
            "o-1",
            "u-2",
            "m-3",
            "eur",
            9.99
        );
        $payment
            ->setTsNonce(98262535)
            ->setRedirectUrls("http://success", "http://failure")
            ->setDescription("the payment")
            ->setPhone("+123245")
            ->setEmail("foo@bar.baz");

        $payment->addCustomKeyValue('custom_some_id', '89');
        $payment->addCustom([
            "custom_mode" => "foo",
            "custom_sale_id" => "9999",
        ]);

        $this->assertSame(
            'order_id=o-1&user_id=u-2&amount=9.99&currency_iso=EUR&mcc=m-3&ts_nonce=98262535'
            . '&success_url=http%3A%2F%2Fsuccess&failure_url=http%3A%2F%2Ffailure'
            . '&description=the+payment&phone=%2B123245&email=foo%40bar.baz'
            . '&custom_some_id=89&custom_mode=foo&custom_sale_id=9999',
            $payment->buildQuery()
        );
    }
}
