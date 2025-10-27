<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use App\Models\User;

class AuthController extends Controller
{
    use HasApiTokens;

    /**
     * Login user and create access token
     */
    public function login(Request $request)
    {
        // Debug temporaire
        \Log::info('Login attempt', [
            'all_data' => $request->all(),
            'json_data' => $request->json()->all(),
            'input_email' => $request->input('email'),
            'input_password' => $request->input('password'),
            'is_json' => $request->isJson(),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Pour les requêtes JSON, récupérer les données correctement
        $rawContent = $request->getContent();
        $jsonData = json_decode($rawContent, true) ?: [];

        $email = $jsonData['email'] ?? null;
        $password = $jsonData['password'] ?? null;

        // Debug temporaire
        \Log::info('Login data received', [
            'email' => $email,
            'password' => $password ? '***' : null,
            'is_json' => $request->isJson(),
            'content_type' => $request->header('Content-Type'),
            'raw_content' => $rawContent,
            'json_data' => $jsonData
        ]);

        // Validation manuelle pour les tests
        if (empty($email) || empty($password)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ]
            ], 422);
        }

        // Validation
        $validator = \Validator::make([
            'email' => $email,
            'password' => $password
        ], [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            \Log::info('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        \Log::info('Attempting auth with credentials', ['email' => $credentials['email']]);

        if (!Auth::attempt($credentials)) {
            \Log::info('Auth failed for user', ['email' => $credentials['email']]);
            return response()->json(['message' => 'Invalid credentials'], 400);
        }

        $user = Auth::user();
        \Log::info('Auth successful for user', ['email' => $user->email, 'id' => $user->id]);

        // Create token via Passport
        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    /**
     * Return the authenticated user
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout (revoke all tokens)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->tokens()) {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Logout successful'], 200);
    }

    /**
     * Refresh access token (optional simulation)
     */
    public function refresh(Request $request)
    {
        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $request->user()->createToken('API Token')->accessToken
        ]);
    }
}
