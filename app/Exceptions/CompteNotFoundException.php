<?php

namespace App\Exceptions;

use Exception;

class CompteNotFoundException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Compte non trouvé.',
        ], 404);
    }
}
