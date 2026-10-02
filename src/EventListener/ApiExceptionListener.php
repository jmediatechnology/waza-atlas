<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/** Errors under /api come back as {"error": "...", "status": 404} instead of an HTML page. */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }
        $e = $event->getThrowable();
        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $message = $status < 500 ? $e->getMessage() : 'Something went wrong on the server.';

        $response = new JsonResponse(['error' => $message, 'status' => $status], $status,
            $e instanceof HttpExceptionInterface ? $e->getHeaders() : []);
        $response->setEncodingOptions(\JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        $event->setResponse($response);
    }
}
