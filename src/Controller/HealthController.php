<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/** Used by the container healthcheck and load balancers. Checks that the database answers. */
final class HealthController
{
    #[Route('/healthz', name: 'health', methods: ['GET'])]
    public function __invoke(Connection $db): JsonResponse
    {
        try {
            $db->executeQuery('SELECT 1')->fetchOne();
        } catch (\Throwable) {
            return new JsonResponse(['status' => 'error', 'database' => 'unreachable'], 503);
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
