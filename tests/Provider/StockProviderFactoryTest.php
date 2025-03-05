<?php

namespace App\Tests\Provider;

use App\Provider\AlphaVantageProvider;
use App\Provider\StockProviderFactory;
use App\Provider\StockProviderInterface;
use App\Provider\StooqProvider;
use PHPUnit\Framework\TestCase;

class StockProviderFactoryTest extends TestCase
{
    private StockProviderFactory $factory;

    private StockProviderInterface $alphaProviderMock;

    private StockProviderInterface $stooqProviderMock;

    protected function setUp(): void
    {
        $this->alphaProviderMock = $this->getMockBuilder(AlphaVantageProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stooqProviderMock = $this->getMockBuilder(StooqProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new StockProviderFactory([
            $this->alphaProviderMock,
            $this->stooqProviderMock
        ]);
    }

    public function testGetProviderReturnsAlphaVantageByDefault(): void
    {
        $provider = $this->factory->getProvider(AlphaVantageProvider::IDENTIFIER);
        $this->assertSame($this->alphaProviderMock, $provider);
    }

    public function testGetProviderReturnsStooqProvider(): void
    {
        $provider = $this->factory->getProvider(StooqProvider::IDENTIFIER);
        $this->assertSame($this->stooqProviderMock, $provider);
    }

    public function testGetProviderReturnsDefaultWhenUnknownIdentifier(): void
    {
        $provider = $this->factory->getProvider('unknown');
        $this->assertSame($this->alphaProviderMock, $provider);
    }
}
