<?php

namespace Genome\Merchant;

/**
 * Defines environment settings.
 */
class Environment
{
    /**
     * @return Environment Production environment settings.
     */
    public static function getProduction(): Environment
    {
        return new Environment(
            'https://pay.genome.eu/'
        );
    }

    /**
     * @var string
     */
    private $hppUrl;

    public function __construct(string $hppUrl)
    {
        $this->hppUrl = $hppUrl;
    }

    /**
     * @return string Hosted payment page URL.
     */
    public function getHppUrl(): string
    {
        return $this->hppUrl;
    }
}
