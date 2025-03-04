<?php

namespace App\Manager;

use App\Entity\QueueMessage;
use App\Entity\User;
use App\Model\StockDto;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Twig\Environment;

class QueueManager
{
    private string $senderMail;

    private string $senderName;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator
    ) {

    }

    public function setSenderMail(string $senderMail): self
    {
        $this->senderMail = $senderMail;

        return $this;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function addToQueue(StockDto $dto, User $user)
    {
        $message = new QueueMessage();
        $message->setEmail($user->getEmail())
            ->setPayload($dto);

        try {
            $this->entityManager->persist($message);
            $this->entityManager->flush();
        } catch (Throwable $ex) {
            $this->logger->error('Could not save message: ' . $ex->getMessage());
        }
    }

    public function processQueue(): array
    {
        $items = $this->entityManager->getRepository(QueueMessage::class)->fetchQueue();
        if ([] === $items) {
            return [];
        }

        $lines = [];
        foreach ($items as $key => $message) {
            $lines[] = $this->sendMail($message);
        }

        return $lines;
    }

    private function sendMail(QueueMessage $message): array
    {

        $htmlContent = $this->twig->render('emails/stock_data.html.twig', [
            'stock' => $message->getPayload()
        ]);

        $address = new Address($this->senderMail, $this->senderName);

        $email = (new Email())
            ->from($address)
            ->to($message->getEmail())
            ->subject($this->translator->trans(id: 'email.subject', domain: 'messages'))
            ->html($htmlContent);

        try {
            $this->mailer->send($email);
            $this->deleteMessage($message);

            return [true, 'Email sent to ' . $message->getEmail() . '.'];
        } catch (Throwable $ex) {
            $this->logger->error('Failed to send email to ' . $message->getEmail() . ':' . $ex->getMessage());
            $this->updateMessage($message);

            return [false, 'Failed to send email to ' . $message->getEmail() . '.'];
        }

    }

    private function updateMessage(QueueMessage $message): void
    {
        try {
            $message->setLastTry();
            $this->entityManager->persist($message);
            $this->entityManager->flush();
        } catch (Throwable $ex) {
            $this->logger->error('Fail to update message:' . $ex->getMessage());
        }
    }

    private function deleteMessage(QueueMessage $message): void
    {
        try {
            $this->entityManager->remove($message);
            $this->entityManager->flush();
        } catch (Throwable $ex) {
            $this->logger->error('Fail to delete message:' . $ex->getMessage());
        }
    }
}
