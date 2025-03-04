<?php

namespace App\Provider;

class StockProviderFactory
{
    /** @var StockProviderInterface[] */
    private array $providers = [];

    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider::IDENTIFIER] = $provider;
        }
    }

    public function getProvider(?string $identifier = null): StockProviderInterface
    {
        if (!isset($this->providers[$identifier])) {
            return $this->providers[AlphaVantageProvider::IDENTIFIER];
        }

        return $this->providers[$identifier];
    }
}
