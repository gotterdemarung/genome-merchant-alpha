<?php

namespace Genome\Merchant;

/**
 * Hosted payment page manager.
 */
class HostedPaymentPageManager
{
    public const
        SIGNATURE_MODE_A = 'MODE_A',
        SIGNATURE_MODE_A_TS = 'MODE_A_TS';

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
     * @var string
     */
    private $redirectSignatureMode;

    /**
     * Constructor.
     * This constructor should not be invoked directly, use getter from MerchantAccountManager instead.
     *
     * @param MerchantAccountManager $merchant
     * @param string $api_key
     * @param string $api_secret
     * @param string $redirectSignatureMode
     */
    public function __construct(
        MerchantAccountManager $merchant,
        string                 $api_key,
        string                 $api_secret,
        string                 $redirectSignatureMode
    )
    {
        $this->merchant = $merchant;
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->redirectSignatureMode = $redirectSignatureMode;
    }

    /**
     * Generates HPP initialization signature in MODE_A format.
     *
     * @param HostedPayment $hostedPayment
     * @return string Generated signature.
     * @throws \Exception On signature generation failure.
     */
    public function generateInitializationSignature(HostedPayment $hostedPayment): string
    {
        switch ($this->redirectSignatureMode) {
            case self::SIGNATURE_MODE_A:
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
            case self::SIGNATURE_MODE_A_TS:
                if (!$hostedPayment->hasTsNonce()) {
                    throw new \Exception('Hosted payment has no TS_NONCE');
                }

                $subject = sprintf(
                    "%s|%s|%d|%.2f|%s|%s|%s|%s",
                    $this->api_secret,
                    "MODE_A_TS",
                    $hostedPayment->getTsNonce(),
                    $hostedPayment->getAmount(),
                    $hostedPayment->getCurrency(),
                    $hostedPayment->getOrderId(),
                    $hostedPayment->getUserId(),
                    $hostedPayment->getMcc()
                );
                return hash("sha256", $subject);
            default:
                throw new \Exception('Invalid redirect signature mode ' . $this->redirectSignatureMode);
        }
    }

    public function generateInitializationRedirectUrl(HostedPayment $hostedPayment): string
    {
        $query = $hostedPayment->buildQuery();
        $query .= '&api_key=' . $this->api_key;
        $query .= '&signature=' . $this->generateInitializationSignature($hostedPayment);

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