<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Trait\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    use ResponseTrait;
    public function sendOtp($email)
    {
        try {
            /*$validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
            }*/

            $otp = rand(100000, 999999); // Generate 6-digit OTP
            $expiresAt = Carbon::now()->addMinutes(10);

            // Save OTP in database
            Otp::updateOrCreate(
                ['email' => $email],
                ['otp' => $otp, 'expires_at' => $expiresAt]
            );

            // Send OTP via email
            Mail::raw("Your OTP code is: $otp", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your OTP Code');
            });

            return $this->sendResponse('', 'OTP sent successfully.', '', 200);
        } catch (\Exception $e) {
            return $this->sendError('Failed to send OTP', ['error' => $e->getMessage()], 500);
        }
    }


    public function resendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
            }

            $otp = rand(100000, 999999); // Generate 6-digit OTP
            $expiresAt = Carbon::now()->addMinutes(10);

            // Save OTP in database
            Otp::updateOrCreate(
                ['email' => $request->email],
                ['otp' => $otp, 'expires_at' => $expiresAt]
            );

            // Send OTP via email
            Mail::raw("Your OTP code is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Your OTP Code');
            });
            return $this->sendResponse('', 'OTP sent successfully.'. $otp, '', 200);
        }catch (\Exception $e) {
            return $this->sendError('Failed to send OTP', ['error' => $e->getMessage()], 500);
        }
    }



    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        $otpRecord = Otp::where('email', $request->email)->where('otp', $request->otp)->first();

        if (!$otpRecord) {
            return $this->sendError('Invalid OTP', [], 400);
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            return $this->sendError('OTP expired', [], 400);
        }

        try {
            // Check if user already exists
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendResponse([], 'No user found for this email');
            }
            // update User
            $user->update([
                'email' => $request->email,
                'email_verified_at' => now(),
                'is_verified' => true,
            ]);

            // Delete OTP after successful verification
            $otpRecord->delete();

            // Generate token and login user
            $token = auth('api')->login($user);

            // Prepare success response
            $success = [
                'id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'is_verified' => $user->is_verified,
            ];

            // Evaluate the message based on the $isNewUser condition
            $message = 'User logged in successfully';


            return $this->sendResponse($success, $message, $token);

        } catch (\Exception $e) {
            return $this->sendError('User creation failed', ['error' => $e->getMessage()], 500);
        }
    }


    // Step 6: Cleanup Expired OTPs
    public function cleanupExpiredOtps()
    {
        Otp::where('expires_at', '<', now())->delete();
    }
}
