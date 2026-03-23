<?php

namespace App\Traits;

use App\Enums\ApiStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Operación exitosa', int $code = 200)
    {
        $response = [
            'status' => ApiStatus::SUCCESS->value,
            'code' => $code,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator || $data instanceof Paginator) {

            $response['data'] = $data->items();

            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => method_exists($data, 'lastPage') ? $data->lastPage() : null,
                'per_page' => $data->perPage(),
                'total' => method_exists($data, 'total') ? $data->total() : null,
            ];
        } else {

            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function error(string $message = 'Error', int $code = 400, $data = null)
    {
        return response()->json([
            'status' => ApiStatus::ERROR->value,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
