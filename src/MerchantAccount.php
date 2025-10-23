<?php

namespace Genome\Merchant;

class MerchantAccount
{
    /**
     * @var int
     */
    private $accountId;

    /**
     * @var string
     */
    private $password;

    public function __construct($accountId, $password)
    {
        if (!is_int($accountId)) {
            throw new \InvalidArgumentException('Account ID must be an integer');
        }
        if (!is_string($password)) {
            throw new \InvalidArgumentException('Password must be a string');
        }

        $this->accountId = $accountId;
        $this->password = $password;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getHostedPaymentPage($secret): HostedPaymentPage
    {
        if (!is_string($secret)) {
            throw new \InvalidArgumentException('Secret must be a string');
        }

        return new HostedPaymentPage($this, $secret);
    }
}