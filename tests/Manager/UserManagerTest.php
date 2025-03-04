<?php

namespace App\Tests\Manager;

use App\Entity\User;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManagerTest extends TestCase
{
    private $userManager;

    private $entityManager;

    private $passwordHasher;

    private $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userManager = new UserManager(
            $this->entityManager,
            $this->passwordHasher,
            $this->validator
        );
    }

    public function testCreateUserWithInvalidDataThrowsException(): void
    {
        $user = new User();
        $user->setEmail('invalid-email');
        $user->setRoles([]);
        $user->setPassword('123');

        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'validation.error.email_already_exists',
                null,
                [],
                $user,
                'email',
                $user->getEmail()
            ),
            new ConstraintViolation(
                'validation.error.must_have_at_least_one_role',
                null,
                [],
                $user,
                'roles',
                $user->getRoles()
            ),
            new ConstraintViolation(
                'validation.error.the_password_must_have_at_least_x_characters',
                null,
                [],
                $user,
                'password',
                $user->getPassword()
            ),
        ]);

        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(ValidationFailedException::class);

        $this->userManager->createUser($user);
    }

    public function testCreateUserHashesPasswordAndPersists(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('PlainTextPassword');

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->passwordHasher->expects($this->once())
                ->method('hashPassword')
                ->with($user, 'PlainTextPassword')
                ->willReturn('hashed-password');

        $this->passwordHasher->expects($this->once())
                ->method('hashPassword')
                ->willReturnCallback(function ($user, $password) {
                    $user->setPassword('hashed-password');

                    return 'hashed-password';
                });

        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush')
                ->willReturnCallback(function () use ($user) {
                    $reflection = new ReflectionClass($user);
                    $property = $reflection->getProperty('id');
                    $property->setAccessible(true);
                    $property->setValue($user, 1);
                });

        $createdUser = $this->userManager->createUser($user);

        $this->assertEquals('hashed-password', $createdUser->getPassword(), 'Password should be hashed.');
        $this->assertNotNull($createdUser->getId(), 'User should have an ID after creation.');
        $this->assertEquals(1, $createdUser->getId(), 'User ID should be 1 after flush().');
    }
}
