<?php

namespace App\Entity;

use App\Repository\StockRequestHistoryRepository;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockRequestHistoryRepository::class)]
class StockRequestHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $provider = null;

    #[Groups(['history'])]
    #[ORM\Column(length: 60)]
    private ?string $symbol = null;

    #[Groups(['history'])]
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $name = null;

    #[Groups(['history'])]
    #[ORM\Column(nullable: true)]
    private ?float $open = null;

    #[Groups(['history'])]
    #[ORM\Column(nullable: true)]
    private ?float $high = null;

    #[Groups(['history'])]
    #[ORM\Column(nullable: true)]
    private ?float $low = null;

    #[Groups(['history'])]
    #[ORM\Column(nullable: true)]
    private ?float $close = null;

    #[Groups(['history'])]
    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct()
    {
        $this->date = new DateTime('now', new DateTimeZone('UTC'));

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getOpen(): ?float
    {
        return $this->open;
    }

    public function setOpen(?float $open): static
    {
        $this->open = $open;

        return $this;
    }

    public function getHigh(): ?float
    {
        return $this->high;
    }

    public function setHigh(?float $high): static
    {
        $this->high = $high;

        return $this;
    }

    public function getLow(): ?float
    {
        return $this->low;
    }

    public function setLow(?float $low): static
    {
        $this->low = $low;

        return $this;
    }

    public function getClose(): ?float
    {
        return $this->low;
    }

    public function setClose(?float $close): static
    {
        $this->close = $close;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
