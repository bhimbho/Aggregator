<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    protected function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function error(string $message = 'An error occurred', mixed $errors = null, int $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Data retrieved successfully', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $statusCode);
    }

    protected function validationError(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

