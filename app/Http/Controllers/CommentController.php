<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, string $id)
    {
        // Valida los datos de la solicitud
        $request->validate([
            'content' => 'required|string|max:255',
        ]);

        // Obtiene el usuario autenticado
        $user = Auth::user();

        // Crea el comentario
        $comment = new Comment();
        $comment->content = $request->input('content');
        $comment->user_id = $user->id;
        $comment->task_id = $id;
        $comment->save();

        // Devuelve mensaje de éxito
        return response()->json(['message' => 'Comentario creado exitosamente'], 201);
    }
}
