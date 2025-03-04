<?php

namespace App\Provider;

use App\Entity\User;
use App\Model\StockDto;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AlphaVantageProvider extends AbstractStockProvider implements StockProviderInterface
{
    public const IDENTIFIER = 'alpha';

    protected string $url = 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=%s&apikey=%s';

    public function process(string $json, User $user): StockDto
    {
        $content = null;

        try {
            $content = json_decode(json: $json, associative: true, flags:\JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Server error: ' . $ex->getMessage(), $ex);
        }

        $quote = $content['Global Quote'] ?? null;
        if (null === $quote || [] === $quote) {
            return new StockDto();
        }

        $definitions = [
            'symbol' => ['01. symbol', false],
            'name' => ['01. symbol', false],
            'open' => ['02. open', true],
            'high' => ['03. high', true],
            'low' => ['04. low', true],
            'close' => ['08. previous close', true],
        ];

        return $this->processDto(toProcess:$quote, definitions: $definitions, user: $user);
    }
}
