<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Token;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Crear nuevo usuario
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // Validación de datos
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ]);

        // Generar token de 6 dígitos
        $token = mt_rand(100000, 999999);

        // Crear usuario
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt(Str::random(8)),
            'role' => 'user',
        ]);

        Token::create([
            'token' => $token,
            'user_id' => $user->id
        ]);

        // Enviar email de bienvenida con token e instrucciones

        $content = "Bienvenido $user->name, Por favor establezca su password usando el token: $token";

            Mail::raw($content, function ($message) use ($request) {
                $message->to($request->email)->subject('Bienvenido');
            });

        return response()->json(['message' => 'User created successfully'], 201);
    }

    /**
     * Encontrar usaurio por email
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }
}
