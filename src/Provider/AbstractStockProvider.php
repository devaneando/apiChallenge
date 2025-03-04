<?php

namespace App\Provider;

use App\Model\StockDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractStockProvider implements StockProviderInterface
{
    protected string $apiKey;

    protected string $url = '';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function get(string $symbol): string
    {
        $url = sprintf($this->url, trim($symbol), $this->apiKey);

        return $this->request($url);
    }

    protected function request(string $url): string
    {
        try {
            $response = $this->httpClient->request('GET', $url);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Request failed with status: ' . $response->getStatusCode());
            }

            return $response->getContent(true);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $ex) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Server error: ' . $ex->getMessage(), $ex);
        } catch (TransportExceptionInterface $ex) {
            throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE, 'Network error: ' . $ex->getMessage(), $ex);
        }
    }

    protected function processDto(array $toProcess, array $definitions): StockDto
    {
        $model = new StockDto();

        foreach ($definitions as $key => $definition) {
            if (null !== $value = $toProcess[$definition[0]] ?? null) {
                $value = $definition[1] ? (float) $value : $value;
                $setter = 'set' . ucfirst($key);
                $model->$setter($value);
            }

        }

        return $model;

    }
}
