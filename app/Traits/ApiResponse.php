<?php

namespace App\Traits;

trait ApiResponse
{
    public function success(array $data = [], string $message = 'OK', int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }

    public function error(string $message = 'Error', int $status = 500): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
