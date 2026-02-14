<?php

namespace App\Traits;

use App\Enums\ApiStatus;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Operación exitosa', int $code = 200)
    {
        return response()->json([
            'status' => ApiStatus::SUCCESS,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error(string $message = 'Error', int $code = 400, $data = null)
    {
        return response()->json([
            'status' => ApiStatus::ERROR,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
