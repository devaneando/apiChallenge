<?php

namespace App\Provider;

use App\Entity\User;
use App\Model\StockDto;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StooqProvider extends AbstractStockProvider implements StockProviderInterface
{
    public const IDENTIFIER = 'stooq';

    protected string $url = 'https://stooq.com/q/l/?s=%s&f=%s&h&e=json';

    public function process(string $json, User $user): StockDto
    {
        $content = null;

        try {
            $content = json_decode(json: $json, associative: true, flags:\JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Server error: ' . $ex->getMessage(), $ex);
        }

        $quote = $content['symbols'] ?? null;

        if (null === $quote || [] === $quote) {
            return new StockDto();
        }
        $quote = reset($quote);

        $definitions = [
            'symbol' => ['symbol', false],
            'name' => ['name', false],
            'open' => ['open', true],
            'high' => ['high', true],
            'low' => ['low', true],
            'close' => ['close', true],
        ];

        return $this->processDto(toProcess:$quote, definitions: $definitions, user: $user);
    }
}
