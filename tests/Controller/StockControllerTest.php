<?php

namespace App\Tests\Controller;

use App\Entity\StockRequestHistory;
use App\Entity\User;
use App\Model\StockDto;
use App\Provider\AlphaVantageProvider;
use App\Provider\StockProviderFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class StockControllerTest extends WebTestCase
{
    private $client;

    private $factoryMock;

    private $providerMock;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        putenv('API_KEY_ALPHAVANTAGE=FAKE_TEST_KEY');
        $_ENV['API_KEY_ALPHAVANTAGE'] = 'FAKE_TEST_KEY';
        $_SERVER['API_KEY_ALPHAVANTAGE'] = 'FAKE_TEST_KEY';

        $this->providerMock = $this->createMock(AlphaVantageProvider::class);
        $this->providerMock->method('get')->willReturn(json_encode([
            'Global Quote' => [
                '01. symbol' => 'AAPL',
                '02. open' => '150.00',
                '03. high' => '155.00',
                '04. low' => '149.00',
                '08. previous close' => '152.50'
            ]
        ]));

        $stockDto = new StockDto();
        $stockDto->setSymbol('AAPL')
            ->setOpen(150.00)
            ->setHigh(155.00)
            ->setLow(149.00)
            ->setClose(152.50);

        $this->providerMock->method('process')->willReturn($stockDto);

        $this->factoryMock = $this->createMock(StockProviderFactory::class);
        $this->factoryMock->method('getProvider')->willReturn($this->providerMock);

        static::getContainer()->set(StockProviderFactory::class, $this->factoryMock);
    }

    private function authenticateClient(): void
    {
        $email = 'testuser@example.com';
        $password = 'SecurePass123!';

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$existingUser) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(password_hash($password, \PASSWORD_BCRYPT));

            $entityManager->persist($user);
            $entityManager->flush();
        }

        $this->client->request('POST', '/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        if (!isset($data['token'])) {
            throw new Exception('Failed to retrieve JWT token. Response: ' . $response->getContent());
        }

        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $data['token']);
    }

    public function testStockEndpointReturnsStockData(): void
    {
        $this->authenticateClient();

        $this->client->request('GET', '/stock?q=AAPL');

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('symbol', $data);
        $this->assertEquals('AAPL', $data['symbol']);
    }

    public function testHistoryEndpointReturnsUserHistory(): void
    {
        $this->authenticateClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'testuser@example.com']);

        $history = new StockRequestHistory();
        $history->setUser($user);
        $history->setSymbol('AAPL');
        $history->setName('Apple Inc.');
        $history->setOpen(150.00);
        $history->setHigh(155.00);
        $history->setLow(149.00);
        $history->setClose(152.50);
        $history->setProvider('alpha');

        $entityManager->persist($history);
        $entityManager->flush();

        $this->client->request('GET', '/history');

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('symbol', $data[0]);
        $this->assertEquals('AAPL', $data[0]['symbol']);
        $this->assertArrayHasKey('date', $data[0]);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/', $data[0]['date']);
    }
}
