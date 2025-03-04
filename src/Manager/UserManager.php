<?php

namespace App\Manager;

use App\Entity\User;
use App\Exceptions\UserExistsException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {

    }

    /**
     * @throws UserExistsException
     * @throws ValidationFailedException
     */
    public function createUser(User $user): User
    {
        // The controller already validate this, but lets assume somebody else decided to use this manager elsewhere.
        if (null !== $user->getId() ?? null) {
            throw new UserExistsException();
        }

        // The controller already validate this, but lets assume somebody else decided to use this manager elsewhere.
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new ValidationFailedException($user, $errors);
        }

        $hash = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hash);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
