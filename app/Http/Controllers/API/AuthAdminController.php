<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\LoginRequest;
use App\Models\Admin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthAdminController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $admin->createToken('main')->plainTextToken;

        return response(compact('admin', 'token'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens if needed
        // $admin->tokens()->delete();

        $token = $admin->createToken('main')->plainTextToken;

        return response(compact('admin', 'token'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if admin exists
        $admin = Admin::where('email', $request->email)->first();
        if (!$admin) {
            return response()->json([
                'message' => 'We cannot find an admin with that email address.'
            ], 404);
        }

        // Send reset password link
        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email']);
        }

        return response()->json(['message' => 'Unable to send password reset link'], 500);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully']);
        }

        return response()->json([
            'message' => 'Invalid token provided'
        ], 400);
    }

    public function logout(Request $request)
    {
        $request->user('admin')->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'admin' => $request->user('admin'),
            'token' => $request->bearerToken() // This returns the current token
        ]);
    }
}
