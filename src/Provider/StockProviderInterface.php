<?php

namespace App\Provider;

use App\Model\StockDto;

interface StockProviderInterface
{
    public function setApiKey(string $apiKey): void;

    public function get(string $symbol): string;

    public function process(string $json): StockDto;
}
