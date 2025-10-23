<?php

namespace Genome\Merchant;

class HostedPaymentPageManager
{
    /**
     * @var MerchantAccountManager
     */
    private $merchant;

    /**
     * @var string
     */
    private $secret;

    public function __construct($merchant, $secret)
    {
        if (!($merchant instanceof MerchantAccountManager)) {
            throw new \InvalidArgumentException('$merchant must be a MerchantAccount');
        }
        if (!is_string($secret)) {
            throw new \InvalidArgumentException('Secret must be a string');
        }

        $this->merchant = $merchant;
        $this->secret = $secret;
    }

    /**
     * Generates HPP initialization signature in MODE_A format.
     *
     * @param float $amount Payment amount.
     * @param string $currency Payment currency.
     * @param string $order_id Unique payment order identifier.
     * @param string $user_id Payee user identifier.
     * @param string $mcc Merchant category code.
     * @return string Generated signature.
     */
    public function generateInitializationSignatureMODE_A(
        float  $amount,
        string $currency,
        string $order_id,
        string $user_id,
        string $mcc
    ): string
    {
        $subject = sprintf(
            "%s|%s|%.2f|%s|%s|%s|%s",
            $this->secret,
            "MODE_A",
            $amount,
            $currency,
            $order_id,
            $user_id,
            $mcc
        );

        return hash("sha256", $subject);
    }

    /**
     * Asserts that callback has correct signature.
     *
     * @param array $headers HTTP request headers.
     * @param string $body HTTP request body.
     * @return void
     * @throws \Exception If signature not correct.
     */
    public function assertCallbackSignature($headers, $body)
    {
        if (!is_array($headers)) {
            throw new \InvalidArgumentException('headers must be an array');
        }
        if (!is_string($body)) {
            throw new \InvalidArgumentException('body must be a string');
        }

        if (!isset($headers['X-Signature-Algorithm'])) {
            throw new \Exception('Missing "X-Signature-Algorithm" header');
        }
        if (strcasecmp($headers['X-Signature-Algorithm'], 'HmacSHA256') !== 0) {
            throw new \Exception('Unsupported signature algorithm ' . $headers['X-Signature-Algorithm']);
        }
        if (!isset($headers['X-Signature'])) {
            throw new \Exception('Missing "X-Signature" header');
        }

        $expected = $headers['X-Signature'];
        $actual = hash_hmac('sha256', $body, $this->secret, false);

        if (strcasecmp($expected, $actual) !== 0) {
            throw new \Exception('Invalid signature');
        }
    }
}