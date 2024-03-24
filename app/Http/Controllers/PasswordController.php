<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Support\Facades\Mail;
use App\Models\Token;
use App\Models\User;

class PasswordController extends Controller
{
    protected $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    /**
     * Olvide mi password
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $user = $this->userController->getUserByEmail($request->email);
        if (!$user) {
            return response()->json(['error' => 'No se encontró ningún usuario con esa dirección de correo electrónico.'], 404);
        }

        try {
            $token = mt_rand(100000, 999999);

            // Verificar si el usuario ya tiene un token
            $existingToken = Token::where('user_id', $user->id)->first();

            if ($existingToken) {
                // Si ya tiene un token, actualiza el token existente
                $existingToken->update([
                    'token' => $token
                ]);
            } else {
                // Si no tiene un token, crea uno nuevo
                Token::create([
                    'token' => $token,
                    'user_id' => $user->id
                ]);
            }

            $content = "Hola $user->name, su token es para reestablecer el password es: $token";
            // Enviar email con token e instrucciones
            Mail::raw($content, function ($message) use ($request) {
                $message->to($request->email)->subject('Contraseña temporal');
            });

            return response()->json(['message' => 'Correo electrónico de restablecimiento de contraseña enviado'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'No se pudo enviar el correo electrónico de restablecimiento de contraseña. Por favor, inténtelo de nuevo más tarde.'], 500);
        }
    }

    /**
     * Cambiar mi password
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:8',
        ]);

        // Buscar el usuario por su email
        $user = User::where('email', $request->email)->first();

        // Si no se encuentra devolver msj de err
        if (!$user) {
            return response()->json(['error' => 'No se encontró ningún usuario con esa dirección de correo electrónico.'], 404);
        }

        // Verificar si el token existe y le pertenece al user
        $token = Token::where('user_id', $user->id)->where('token', $request->token)->first();

        // Si el token no es válido devolver msj de error
        if (!$token) {
            return response()->json(['error' => 'El token proporcionado no es válido para este usuario.'], 400);
        }

        // Actualizar el password del usuario
        $user->password = bcrypt($request->password);
        $user->save();

        // Eliminar el token de la base de datos
        $token->delete();

        return response()->json(['message' => 'Contraseña actualizada con éxito.'], 200);
    }

}
