<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario (por defecto, como cliente).
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'telefono' => 'required|string|min:10|max:15',
        ]);

        $clienteRole = Role::where('nombre', 'cliente')->firstOrFail();

        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'role_id' => $clienteRole->id,
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente.'
        ], 201);
    }

    /**
     * Autentica un usuario y devuelve un token.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas son incorrectas.'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = [
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('role')
        ];

        // Si el usuario es barbero, incluir el id del barbero
        if ($user->role && $user->role->nombre === 'barbero' && $user->barbero) {
            $response['barbero_id'] = $user->barbero->id;
        }

        return response()->json($response);
    }

    /**
     * Cierra la sesión del usuario (revoca el token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }
}
