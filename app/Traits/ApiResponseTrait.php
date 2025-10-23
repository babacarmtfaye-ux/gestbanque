<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Format de réponse API standardisée
     */
    protected function apiResponse($data = null, $message = '', $status = 200, $success = true)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Réponse de succès
     */
    protected function successResponse($data = null, $message = 'Opération réussie', $status = 200)
    {
        return $this->apiResponse($data, $message, $status, true);
    }

    /**
     * Réponse d'erreur
     */
    protected function errorResponse($message = 'Une erreur est survenue', $status = 400, $data = null)
    {
        return $this->apiResponse($data, $message, $status, false);
    }
}
