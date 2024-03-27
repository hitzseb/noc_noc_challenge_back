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
            $token = Str::random(12);

            // Verifica si el usuario ya tiene un token
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

            $content = "Hola $user->name, para restablecer su contraseña, haga clic en el siguiente enlace: " .
           url('http://localhost:8080/update-password?token=' . $token);
            // Envia un email con link para actualizar el password
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
    public function updatePassword(Request $request, $token) {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        // Buscar al usuario por su email
        $user = User::where('email', $request->email)->first();

        // Si no se encuentra devuelve msj de error
        if (!$user) {
            return response()->json(['error' => 'No se encontró ningún usuario con esa dirección de correo electrónico.'], 404);
        }

        // Verifica si el token existe y le pertenece al usuario
        $tokenModel = Token::where('user_id', $user->id)->where('token', $token)->first();

        // Si el token no es válido devuelve msj de error
        if (!$tokenModel) {
            return response()->json(['error' => 'El token proporcionado no es válido para este usuario.'], 400);
        }

        // Actualiza el password del usuario
        $user->password = bcrypt($request->password);
        $user->save();

        // Elimina el token de la base de datos
        $tokenModel->delete();

        return response()->json(['message' => 'Contraseña actualizada con éxito.'], 200);
    }

}
