<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/** JSON with readable kanji and slashes, so "踵返" stays "踵返" on the wire. */
trait JsonResponder
{
    private function respond(mixed $data, int $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        $response->setEncodingOptions(\JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);

        return $response;
    }
}
