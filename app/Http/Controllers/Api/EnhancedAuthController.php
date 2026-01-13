<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $key = 'register.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Too many registration attempts. Please try again later.'],
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:student,faculty,staff,admin',
            'phone' => 'nullable|string|max:15',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'phone' => $request->phone,
        ]);

        // Assign default role based on user type
        $role = Role::firstOrCreate(['name' => $request->user_type, 'guard_name' => 'web']);
        $user->assignRole($role);

        $token = $user->createToken('API Token', ['*'], now()->addDays(7))->plainTextToken;

        RateLimiter::hit($key);

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
            'expires_at' => now()->addDays(7)->toISOString(),
        ], 201);
    }

    public function login(Request $request)
    {
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again later.'],
            ]);
        }

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        
        // Revoke old tokens
        $user->tokens()->delete();
        
        $token = $user->createToken('API Token', ['*'], now()->addDays(7))->plainTextToken;

        ActivityLogger::login();
        RateLimiter::clear($key);

        return response()->json([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
            'expires_at' => now()->addDays(7)->toISOString(),
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('API Token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'token' => $token,
            'expires_at' => now()->addDays(7)->toISOString(),
        ]);
    }

    public function logout(Request $request)
    {
        ActivityLogger::logout();
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out from all devices']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions'),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles')
        ]);
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->givePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permission assigned successfully',
            'user' => $user->load('permissions')
        ]);
    }
}