<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    #[OA\Post(
        path: '/api/v1/auth/register',
        operationId: 'register',
        summary: 'Register a new user with email',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '9876543210'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success - OTP Sent'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        $message = 'Registration initiated. OTP sent to your email successfully.';

        if (!app()->environment('production')) {
            $message .= " (OTP: {$result['otp']})";
        }

        return ApiResponse::success($message);
    }

    #[OA\Post(
        path: '/api/v1/auth/verify-otp',
        operationId: 'verifyOtp',
        summary: 'Verify Email OTP to complete registration',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp', 'device_id'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'abc-123-def'),
                    new OA\Property(property: 'device_name', type: 'string', nullable: true, example: 'Samsung Galaxy S24'),
                    new OA\Property(property: 'device_type', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'fcm_token', type: 'string', nullable: true, example: 'fcm_xxxxxxxxx'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 422, description: 'Validation Error or Invalid OTP'),
        ]
    )]
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->authService->verifyOtp(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(
            'Registration successful and authenticated',
            [
                'token' => $result['token'],
                'is_profile_complete' => $result['is_profile_complete'],
                'user' => $result['user']->toApiResponse(),
            ]
        );
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        operationId: 'login',
        summary: 'Login with Email and Password',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'device_id'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'abc-123-def'),
                    new OA\Property(property: 'device_name', type: 'string', nullable: true),
                    new OA\Property(property: 'device_type', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'fcm_token', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(
            'Login successful',
            [
                'token' => $result['token'],
                'is_profile_complete' => $result['is_profile_complete'],
                'user' => $result['user']->toApiResponse(),
            ]
        );
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'logout',
        summary: 'Logout current device',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(
            'Logged out successfully'
        );
    }

    #[OA\Post(
        path: '/api/v1/auth/logout-all',
        operationId: 'logoutAll',
        summary: 'Logout all devices',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logoutAll(Request $request)
    {
        $this->authService->logoutAllDevices($request->user());

        return ApiResponse::success(
            'Logged out from all devices successfully'
        );
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'me',
        summary: 'Get authenticated user profile',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request)
    {
        return ApiResponse::success(
            'Profile fetched successfully',
            [
                'user' => $request->user()->toApiResponse(),
            ]
        );
    }
}
