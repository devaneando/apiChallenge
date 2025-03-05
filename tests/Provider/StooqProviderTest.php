<?php

namespace App\Tests\Provider;

use App\Entity\User;
use App\Manager\QueueManager;
use App\Model\StockDto;
use App\Provider\StooqProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class StooqProviderTest extends TestCase
{
    private StooqProvider $provider;

    private MockHttpClient $httpClient;

    private EntityManagerInterface $entityManagerMock;

    private LoggerInterface $loggerMock;

    private QueueManager $queueManagerMock;

    protected function setUp(): void
    {
        $responseContent = json_encode([
            'symbols' => [
                [
                    'symbol' => 'AAPL',
                    'name' => 'Apple Inc.',
                    'open' => '150.00',
                    'high' => '155.00',
                    'low' => '149.00',
                    'close' => '152.50'
                ]
            ]
        ]);

        $mockResponse = new MockResponse($responseContent, [
            'http_code' => 200,
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->httpClient = new MockHttpClient(fn (string $method, string $url): ResponseInterface => $mockResponse);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->queueManagerMock = $this->createMock(QueueManager::class);

        $this->provider = new StooqProvider(
            $this->httpClient,
            $this->entityManagerMock,
            $this->loggerMock,
            $this->queueManagerMock
        );
        $this->provider->setApiKey('FAKE_API_KEY');
    }

    public function testGetReturnsJson(): void
    {
        $responseJson = $this->provider->get('AAPL');

        $this->assertJson($responseJson);
        $this->assertStringContainsString('"symbols"', $responseJson);
    }

    public function testProcessReturnsStockDto(): void
    {
        $responseJson = json_encode([
            'symbols' => [
                [
                    'symbol' => 'AAPL',
                    'name' => 'Apple Inc.',
                    'open' => '150.00',
                    'high' => '155.00',
                    'low' => '149.00',
                    'close' => '152.50'
                ]
            ]
        ]);

        $userMock = $this->createMock(User::class);

        $stockDto = $this->provider->process($responseJson, $userMock);

        $this->assertInstanceOf(StockDto::class, $stockDto);
        $this->assertSame('AAPL', $stockDto->getSymbol());
        $this->assertSame('Apple Inc.', $stockDto->getName());
        $this->assertSame(150.00, $stockDto->getOpen());
        $this->assertSame(155.00, $stockDto->getHigh());
        $this->assertSame(149.00, $stockDto->getLow());
        $this->assertSame(152.50, $stockDto->getClose());
    }

    public function testProcessWithInvalidJsonThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $userMock = $this->createMock(User::class);
        $this->provider->process('{invalid json', $userMock);
    }

    public function testGetHandlesHttpError(): void
    {
        $httpClient = new MockHttpClient(fn () => new MockResponse('Error', ['http_code' => 500]));

        $provider = new StooqProvider(
            $httpClient,
            $this->entityManagerMock,
            $this->loggerMock,
            $this->queueManagerMock
        );
        $provider->setApiKey('FAKE_API_KEY');

        $this->expectException(HttpException::class);
        $provider->get('AAPL');
    }
}
