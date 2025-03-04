<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['email'],
    message: 'validation.error.email_already_exists'
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['safe'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['safe'])]
    #[Assert\NotBlank(message:'validation.error.email_is_blank')]
    #[Assert\Email(message:'validation.error.email_is_invalid')]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[Groups(['safe'])]
    #[Assert\Count(min: 1, minMessage: 'validation.error.must_have_at_least_one_role')]
    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    #[Assert\PasswordStrength(
        minScore: Assert\PasswordStrength::STRENGTH_WEAK,
        message: 'validation.error.the_password_is_too_weak'
    )]
    #[Assert\Length(
        min: 8,
        minMessage: 'validation.error.the_password_must_have_at_least_x_characters'
    )]
    #[Assert\NotBlank(message:'validation.error.password_is_blank')]
    #[ORM\Column]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
