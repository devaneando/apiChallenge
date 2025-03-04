<?php

namespace App\Tests\Validation;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testInvalidEmail(): void
    {
        $user = new User();
        $user->setEmail('invalid-email');

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, count($errors), 'Expected validation errors for invalid email.');
    }

    public function testBlankEmail(): void
    {
        $user = new User();
        $user->setEmail('');

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, count($errors), 'Expected validation errors for blank email.');
    }

    public function testEmptyRoles(): void
    {
        $user = new User();
        $user->setRoles([]);

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, count($errors), 'Expected validation errors for missing roles.');
    }

    public function testWeakPassword(): void
    {
        $user = new User();
        $user->setPassword('1234567'); // Less than 8 characters, weak password

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, count($errors), 'Expected validation errors for weak password.');
    }

    public function testValidUser(): void
    {
        $user = new User();
        $user->setEmail('valid@email.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('StrongPass123!'); // Assumed strong enough

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors, 'Expected no validation errors for a valid user.');
    }
}
