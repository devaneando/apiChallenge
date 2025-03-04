<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Exceptions\UserExistsException;
use App\Manager\UserManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class UserControllerTest extends WebTestCase
{
    private $client;

    private $userManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userManager = $this->createMock(UserManager::class);

        static::getContainer()->set(UserManager::class, $this->userManager);
    }

    private function authenticateClient(): void
    {
        $email = 'testuser@example.com';
        $password = 'SecurePass123!';

        $entityManager = static::getContainer()->get('doctrine')->getManager();

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

    public function testCreateUserSuccessfully(): void
    {
        $mail = $this->generateUniqueEmail();

        $user = new User();
        $user->setEmail($mail);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('SecurePass123!');

        $this->userManager->method('createUser')->willReturn($user);

        $this->client->request('POST', '/user/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $mail,
            'roles' => ['ROLE_USER'],
            'password' => 'SecurePass123!',
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals($mail, $data['user']['email']);
    }

    public function testCreateUserWithValidationErrors(): void
    {
        $this->client->request('POST', '/user/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => '',
            'roles' => [],
            'password' => '123',
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertNotEmpty($data['errors']);
    }

    public function testCreateUserWhenUserAlreadyExists(): void
    {
        $mail = $this->generateUniqueEmail();

        $this->userManager->method('createUser')
            ->willThrowException(new UserExistsException('User already exists.'));

        $this->client->request('POST', '/user/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $mail,
            'roles' => ['ROLE_USER'],
            'password' => 'SecurePass123!',
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('User already exists.', $data['errors']);
    }

    public function testCreateUserWithValidationException(): void
    {
        $mail = $this->generateUniqueEmail();

        $user = new User();
        $user->setEmail($mail);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('SecurePass123!');

        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'validation.error.the_password_must_have_at_least_x_characters',
                null,
                [],
                $user,
                'password',
                '123'
            ),
        ]);

        $this->userManager->method('createUser')
            ->willThrowException(new ValidationFailedException($user, $violations));

        $this->client->request('POST', '/user/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $mail,
            'roles' => ['ROLE_USER'],
            'password' => '123',
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
    }

    public function testViewUserSuccessfully(): void
    {
        $mail = $this->generateUniqueEmail();

        $user = new User();
        $user->setEmail($mail);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('SecurePass123!');

        static::getContainer()->get('doctrine')->getManager()->persist($user);
        static::getContainer()->get('doctrine')->getManager()->flush();

        $this->authenticateClient();

        $this->client->request('GET', '/user/' . $user->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($mail, $data['email']);
    }

    private function generateUniqueEmail(): string
    {
        return uniqid('test_', true) . '@example.com';
    }
}
