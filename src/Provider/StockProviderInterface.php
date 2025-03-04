<?php

namespace App\Provider;

use App\Entity\User;
use App\Model\StockDto;

interface StockProviderInterface
{
    public function setApiKey(string $apiKey): void;

    public function get(string $symbol): string;

    public function process(string $json, User $user): StockDto;
}
