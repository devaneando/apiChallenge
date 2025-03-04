<?php

namespace App\Tests\Provider;

use App\Provider\AlphaVantageProvider;
use App\Provider\StockProviderFactory;
use App\Provider\StooqProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class StockProviderFactoryTest extends TestCase
{
    private StockProviderFactory $factory;

    private AlphaVantageProvider $alphaProvider;

    private StooqProvider $stooqProvider;

    protected function setUp(): void
    {

        $mockResponse = new MockResponse('{}', ['http_code' => 200]);

        $httpClient = new MockHttpClient(fn (string $method, string $url): ResponseInterface => $mockResponse);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $this->alphaProvider = new AlphaVantageProvider($httpClient, $entityManagerMock, $loggerMock);
        $this->alphaProvider->setApiKey('FAKE_API_KEY');

        $this->stooqProvider = new StooqProvider($httpClient, $entityManagerMock, $loggerMock);
        $this->stooqProvider->setApiKey('FAKE_API_KEY');

        $this->factory = new StockProviderFactory([$this->alphaProvider, $this->stooqProvider]);
    }

    public function testGetProviderReturnsAlphaVantageByDefault(): void
    {
        $provider = $this->factory->getProvider();
        $this->assertSame($this->alphaProvider, $provider);
    }

    public function testGetProviderReturnsAlphaVantageWhenExplicitlyRequested(): void
    {
        $provider = $this->factory->getProvider('alpha');
        $this->assertSame($this->alphaProvider, $provider);
    }

    public function testGetProviderReturnsStooqProvider(): void
    {
        $provider = $this->factory->getProvider('stooq');
        $this->assertSame($this->stooqProvider, $provider);
    }

    public function testGetProviderReturnsDefaultWhenUnknownIdentifier(): void
    {
        $provider = $this->factory->getProvider('unknown');
        $this->assertSame($this->alphaProvider, $provider);
    }
}
