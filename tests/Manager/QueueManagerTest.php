<?php

namespace App\Tests\Manager;

use App\Entity\QueueMessage;
use App\Entity\User;
use App\Manager\QueueManager;
use App\Model\StockDto;
use App\Repository\QueueMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class QueueManagerTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private LoggerInterface $logger;

    private MailerInterface $mailer;

    private Environment $twig;

    private TranslatorInterface $translator;

    private QueueManager $queueManager;

    private QueueMessageRepository $queueMessageRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->queueMessageRepository = $this->createMock(QueueMessageRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->with(QueueMessage::class)
            ->willReturn($this->queueMessageRepository);

        $this->queueManager = new QueueManager(
            $this->entityManager,
            $this->logger,
            $this->mailer,
            $this->twig,
            $this->translator
        );

        $this->queueManager->setSenderMail('sender@example.com');
        $this->queueManager->setSenderName('Sender Name');
    }

    public function testAddToQueue(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');

        $dto = $this->createMock(StockDto::class);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->queueManager->addToQueue($dto, $user);
    }

    public function testProcessQueueWhenEmpty(): void
    {

        $this->queueMessageRepository
            ->method('fetchQueue')
            ->willReturn([]);

        $result = $this->queueManager->processQueue();
        $this->assertEquals([], $result);
    }

    public function testProcessQueueWithMessages(): void
    {
        $queueMessage = $this->createMock(QueueMessage::class);
        $queueMessage->method('getEmail')->willReturn('user@example.com');

        $stockDto = $this->createMock(StockDto::class);
        $queueMessage->method('getPayload')->willReturn($stockDto);

        $this->queueMessageRepository
            ->method('fetchQueue')
            ->willReturn([$queueMessage]);

        $this->twig
            ->method('render')
            ->willReturn('<p>Stock update for IBM</p>');

        $this->translator
            ->method('trans')
            ->willReturn('Stock Update Notification');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(Email::class));

        $result = $this->queueManager->processQueue();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertTrue($result[0][0]);
    }

    public function testSendMailFails(): void
    {
        $queueMessage = $this->createMock(QueueMessage::class);
        $queueMessage->method('getEmail')->willReturn('user@example.com');

        $stockDto = $this->createMock(StockDto::class);
        $queueMessage->method('getPayload')->willReturn($stockDto);

        $this->twig
            ->method('render')
            ->willReturn('<p>Stock update for IBM</p>');

        $this->translator
            ->method('trans')
            ->willReturn('Stock Update Notification');

        $this->mailer
            ->method('send')
            ->willThrowException(new Exception('Mail error'));

        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->queueMessageRepository
            ->method('fetchQueue')
            ->willReturn([$queueMessage]);

        $result = $this->queueManager->processQueue();

        $this->assertIsArray($result);
        $this->assertFalse($result[0][0]);
    }
}
