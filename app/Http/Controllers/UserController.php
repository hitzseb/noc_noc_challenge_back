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
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

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

        // Genera token
        $token = Str::random(12);

        // Crea usuario
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

        // Envia email de bienvenida con link para actualizar password

        $content = "Bienvenido $user->name. Por favor establezca su password en el siguiente enlace: " .
           url('http://localhost:8080/update-password?token=' . $token);

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
