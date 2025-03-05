<?php

namespace App\Entity;

use App\Model\StockDto;
use App\Repository\QueueMessageRepository;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QueueMessageRepository::class)]
#[ORM\Table(name: 'queue_messages')]
class QueueMessage
{
    public const STATUS_PENDING = 0;

    public const STATUS_FAILED = 1;

    public const MAX_TRIES = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $tries = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $lastTry = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    public function __construct()
    {
        $this->setLastTry();
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTries(): ?int
    {
        return $this->tries;
    }

    public function getLastTry(): ?DateTimeInterface
    {
        return $this->lastTry;
    }

    public function setLastTry(?DateTimeInterface $dateTime = null): static
    {
        $this->lastTry = $dateTime ?? new DateTime('now', new DateTimeZone('UTC'));
        $this->tries = ($this->tries ?? 0) + 1;
        if (self::MAX_TRIES <= $this->tries) {
            $this->status = self::STATUS_FAILED;
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPayload(): StockDto
    {
        return StockDto::fromArray($this->payload);
    }

    public function setPayload(StockDto $payload): static
    {
        $this->payload = $payload->toArray();

        return $this;
    }
}
