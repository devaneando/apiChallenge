<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Exceptions\UserExistsException;
use App\Manager\UserManager;
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

    public function testCreateUserSuccessfully(): void
    {
        $mail = $this->generateUniqueEmail();

        $user = new User();
        $user->setEmail($mail);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('SecurePass123!');

        $this->userManager->method('createUser')->willReturn($user);

        $this->client->request('POST', '/api/v01/user/create', [], [], [
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
        $this->client->request('POST', '/api/v01/user/create', [], [], [
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

        $this->client->request('POST', '/api/v01/user/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $mail ,
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

        $this->client->request('POST', '/api/v01/user/create', [], [], [
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
        $user->setEmail($mail );
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('SecurePass123!');

        static::getContainer()->get('doctrine')->getManager()->persist($user);
        static::getContainer()->get('doctrine')->getManager()->flush();

        $this->client->request('GET', '/api/v01/admin/user/' . $user->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($mail, $data['email']);
    }

    public function testDeleteUserSuccessfully(): void
    {
        $mail = $this->generateUniqueEmail();

        $user = new User();
        $user->setEmail($mail );
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('SecurePass123!');

        static::getContainer()->get('doctrine')->getManager()->persist($user);
        static::getContainer()->get('doctrine')->getManager()->flush();

        $this->userManager->expects($this->once())->method('deleteUser')->with($user);

        $this->client->request('DELETE', '/api/v01/admin/user/' . $user->getId() . '/delete', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('User deleted!', $data['message']);
    }

    private function generateUniqueEmail(): string
    {
        return uniqid('test_', true) . '@example.com';
    }
}
