<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponserTrait
{

    protected function success($data, string $message, int $httpResponseCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => null,
            'data' => $data,
            'errors' => null,
        ], $httpResponseCode);
    }

    protected function error(string $message, ?array $errors = [], int $httpResponseCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message ?? null,
            'data' => null,
            'errors' => $errors ?? null,
        ], $httpResponseCode);
    }
}