<?php

namespace App\Provider;

use App\Entity\StockRequestHistory;
use App\Entity\User;
use App\Model\StockDto;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

abstract class AbstractStockProvider implements StockProviderInterface
{
    protected string $apiKey;

    protected string $url = '';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
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

    protected function processDto(array $toProcess, array $definitions, User $user): StockDto
    {
        $model = new StockDto();

        foreach ($definitions as $key => $definition) {
            if (null !== $value = $toProcess[$definition[0]] ?? null) {
                $value = $definition[1] ? (float) $value : $value;
                $setter = 'set' . ucfirst($key);
                $model->$setter($value);
            }

        }

        $this->saveHistory($model, $user);

        return $model;
    }

    public function saveHistory(StockDto $dto, User $user): void
    {
        if (null === $dto->getOpen()) {
            return;
        }

        $history = new StockRequestHistory();
        $history->setSymbol($dto->getSymbol())
            ->setName($dto->getName())
            ->setOpen($dto->getOpen())
            ->setHigh($dto->getHigh())
            ->setLow($dto->getLow())
            ->setClose($dto->getClose())
            ->setProvider(static::IDENTIFIER)
            ->setUser($user);

        try {
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        } catch (Throwable $ex) {
            $this->logger->error(
                'Failed to write history: ' . $ex->getMessage()
            );
        }

    }
}
