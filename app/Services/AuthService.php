<?php

namespace App\Services;

use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\UserBlockedException;
use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {
        $email = $data['email'];

        // Remove any existing pending registration for this email
        PendingUser::where('email', $email)->delete();

        $otp = (string) random_int(100000, 999999);
        $expiryTime = now()->addMinutes(10);

        PendingUser::create([
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'otp' => $otp,
            'otp_expires_at' => $expiryTime,
        ]);

        $messageText = "Your OTP for registration is {$otp}. It is valid for 10 minutes.";

        try {
            Mail::raw($messageText, function ($message) use ($email) {
                $message->to($email)
                        ->subject('Verification OTP - Electron');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email OTP sending failed', ['error' => $e->getMessage()]);
            // Still proceed even if email fails in local dev, but maybe return a warning
        }

        return [
            'otp' => $otp, // returned for debugging in non-prod
        ];
    }

    public function verifyOtp(array $data, string $ip, string $userAgent)
    {
        $pendingUser = PendingUser::where('email', $data['email'])->first();

        if (!$pendingUser) {
            throw new InvalidOtpException('No pending registration found for this email.');
        }

        if (now()->greaterThan($pendingUser->otp_expires_at)) {
            $pendingUser->delete();
            throw new InvalidOtpException('OTP has expired. Please register again.');
        }

        if ($data['otp'] !== $pendingUser->otp) {
            throw new InvalidOtpException('Invalid OTP.');
        }

        // OTP is valid. Move to Users table.
        $user = DB::transaction(function () use ($pendingUser, $data) {
            $newUser = User::create([
                'name' => $pendingUser->name,
                'email' => $pendingUser->email,
                'phone' => $pendingUser->phone,
                'password' => $pendingUser->password,
                'role' => User::ROLE_USER, // default role
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'created_by' => 0, // self registered
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);
            
            $pendingUser->delete();

            return $newUser;
        });

        $user->update([
            'last_login_at' => now(),
            'location_updated_at' => (isset($data['latitude']) && isset($data['longitude'])) ? now() : $user->location_updated_at,
            'latitude' => $data['latitude'] ?? $user->latitude,
            'longitude' => $data['longitude'] ?? $user->longitude,
        ]);

        $token = $this->generateDeviceToken($user, $data, $ip, $userAgent);

        return [
            'token' => $token,
            'user' => $user,
            'is_profile_complete' => !is_null($user->profile_completed_at),
        ];
    }

    public function login(array $data, string $ip, string $userAgent)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided.'],
            ]);
        }

        if ($user->status === User::STATUS_BLOCKED) {
            throw new UserBlockedException(
                $user->blocked_reason ?? 'Your account has been blocked.'
            );
        }

        $user->update([
            'last_login_at' => now(),
            'location_updated_at' => (isset($data['latitude']) && isset($data['longitude'])) ? now() : $user->location_updated_at,
            'latitude' => $data['latitude'] ?? $user->latitude,
            'longitude' => $data['longitude'] ?? $user->longitude,
        ]);

        $token = $this->generateDeviceToken($user, $data, $ip, $userAgent);

        return [
            'token' => $token,
            'user' => $user,
            'is_profile_complete' => !is_null($user->profile_completed_at),
        ];
    }

    public function logout(User $user)
    {
        // Delete ONLY current device token
        $user->currentAccessToken()?->delete();
    }

    public function logoutAllDevices(User $user)
    {
        // Delete ALL tokens
        $user->tokens()->delete();
    }

    private function generateDeviceToken(User $user, array $data, string $ip, string $userAgent)
    {
        $tokenResult = $user->createToken('auth-token');
        
        $accessToken = $tokenResult->accessToken;
        $accessToken->device_id = $data['device_id'] ?? null;
        $accessToken->device_name = $data['device_name'] ?? null;
        $accessToken->device_type = $data['device_type'] ?? null;
        $accessToken->fcm_token = $data['fcm_token'] ?? null;
        $accessToken->latitude = $data['latitude'] ?? null;
        $accessToken->longitude = $data['longitude'] ?? null;
        $accessToken->ip_address = $ip;
        $accessToken->user_agent = $userAgent;
        $accessToken->save();

        return $tokenResult->plainTextToken;
    }
}
