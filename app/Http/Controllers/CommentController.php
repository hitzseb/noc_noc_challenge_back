<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, string $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'content' => 'required|string|max:255', // Ajusta las reglas de validación según tus necesidades
        ]);

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Crear el comentario
        $comment = new Comment();
        $comment->content = $request->input('content');
        $comment->user_id = $user->id;
        $comment->task_id = $id;
        $comment->save();

        // Devolver una respuesta adecuada, por ejemplo, un mensaje de éxito
        return response()->json(['message' => 'Comentario creado exitosamente'], 201);
    }
}
