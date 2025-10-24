<?php

namespace Genome\Merchant;

/**
 * Hosted payment page manager.
 */
class HostedPaymentPageManager
{
    /**
     * @var MerchantAccountManager
     */
    private $merchant;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_secret;

    /**
     * Constructor.
     * This constructor should not be invoked directly, use getter from MerchantAccountManager instead.
     *
     * @param MerchantAccountManager $merchant
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct(MerchantAccountManager $merchant, string $api_key, string $api_secret)
    {
        $this->merchant = $merchant;
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * Generates HPP initialization signature in MODE_A format.
     *
     * @param HostedPayment $hostedPayment
     * @return string Generated signature.
     */
    public function generateInitializationSignatureMODE_A(HostedPayment $hostedPayment): string
    {
        $subject = sprintf(
            "%s|%s|%.2f|%s|%s|%s|%s",
            $this->api_secret,
            "MODE_A",
            $hostedPayment->getAmount(),
            $hostedPayment->getCurrency(),
            $hostedPayment->getOrderId(),
            $hostedPayment->getUserId(),
            $hostedPayment->getMcc()
        );

        return hash("sha256", $subject);
    }

    public function generateInitializationRedirectUrlMODE_A(HostedPayment $hostedPayment): string
    {
        $query = $hostedPayment->buildQuery();
        $query .= '&api_key=' . $this->api_key;
        $query .= '&signature=' . $this->generateInitializationSignatureMODE_A($hostedPayment);

        return $this->merchant->getEnvironment()->getHppUrl() . "?" . $query;
    }

    /**
     * Asserts that callback has correct signature.
     *
     * @param array $headers HTTP request headers.
     * @param string $body HTTP request body.
     * @return void
     * @throws \Exception If signature not correct.
     */
    public function assertCallbackSignature(array $headers, string $body)
    {
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
        $actual = hash_hmac('sha256', $body, $this->api_secret, false);

        if (strcasecmp($expected, $actual) !== 0) {
            throw new \Exception('Invalid signature');
        }
    }
}