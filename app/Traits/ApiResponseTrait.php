<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Return a success response
     */
    protected function successResponse(string $message, $data = null, int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message, array $errors = [], int $statusCode = 400): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(string $message, array $errors = [], int $statusCode = 422): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}