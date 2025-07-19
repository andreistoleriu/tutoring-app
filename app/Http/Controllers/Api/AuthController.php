<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
public function register(Request $request): JsonResponse
{
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:50'],
        'last_name' => ['required', 'string', 'max:50'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'phone' => ['nullable', 'string'],
        'password' => ['required', 'confirmed', 'min:8'],
        'user_type' => ['required', 'in:student,tutor'],
        'location_id' => ['nullable', 'exists:locations,id'],
        'terms_accepted' => ['required', 'accepted'],
        'privacy_accepted' => ['required', 'accepted'],
    ]);

    // Create user
    $user = User::create([
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'phone' => $validated['phone'] ?? null,
        'user_type' => $validated['user_type'],
    ]);

    // Create 14-day free trial subscription for all users
    Subscription::create([
        'user_id' => $user->id,
        'plan_type' => 'free_trial',
        'price' => 0,
        'status' => 'active',
        'started_at' => now(),
        'trial_ends_at' => now()->addDays(14),
        'expires_at' => now()->addDays(14),
        'shows_ads' => true,
    ]);

    // If user is a tutor, create tutor profile
    if ($user->isTutor()) {
        Tutor::create([
            'user_id' => $user->id,
            'location_id' => $validated['location_id'] ?? 1,
            'hourly_rate' => 0, // Will be set during profile setup
        ]);
    }

    // Create API token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'User registered successfully',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'user_type' => $user->user_type,
            'is_tutor' => $user->isTutor(),
            'is_student' => $user->isStudent(),
        ],
        'token' => $token,
        'token_type' => 'Bearer',
        'subscription' => [
            'plan_type' => 'free_trial',
            'days_remaining' => 14,
            'shows_ads' => true,
        ],
    ], 201);
}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Revoke existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load tutor profile if user is a tutor
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'user_type' => $user->user_type,
            'is_tutor' => $user->isTutor(),
            'is_student' => $user->isStudent(),
        ];

        if ($user->isTutor()) {
            $user->load(['tutor.location', 'tutor.subjects', 'subscription']);
            $userData['tutor'] = $user->tutor;
            $userData['subscription'] = $user->subscription;
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'user_type' => $user->user_type,
            'is_tutor' => $user->isTutor(),
            'is_student' => $user->isStudent(),
        ];

        if ($user->isTutor()) {
            $user->load(['tutor.location', 'tutor.subjects', 'subscription']);
            $userData['tutor'] = $user->tutor;
            $userData['subscription'] = $user->subscription;
        }

        return response()->json(['user' => $userData]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
