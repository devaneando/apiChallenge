<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiExceptionListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'An unexpected error occurred.';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } elseif ($exception instanceof ClientExceptionInterface) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $message = 'Client error: ' . $exception->getMessage();
        } elseif ($exception instanceof TransportExceptionInterface) {
            $statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
            $message = 'Network error: ' . $exception->getMessage();
        }

        $this->logger->error(sprintf(
            'API Exception: [%d@%s] %s',
            $statusCode,
            get_class($exception),
            $exception->getMessage()
        ));

        $response = new JsonResponse([
            'error' => $message,
        ], $statusCode);

        $event->setResponse($response);
    }
}
