<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function store(Request $request, string $id)
    {
        // Obtiene el usuario autenticado
        $user = Auth::user();

        // Valida la solicitud de carga de archivos
        $validator = Validator::make($request->all(), [
            'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Verifica si la tarea existe
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['error' => 'La tarea especificada no existe'], 404);
        }

        // Obtiene el archivo cargado
        $file = $request->file('attachment');

        // Guarda el archivo en storage>app>attachments
        $filePath = $file->store('attachments');

        // Crea una nueva instancia de Attachment y guarda el nombre del archivo
        // TODO: cambiar filePath por fileName en todo el programa y en el front
        $attachment = new Attachment();
        $attachment->path = basename($filePath);
        $attachment->task_id = $id;
        $attachment->user_id = $user->id;
        $attachment->save();

        // Devuelve el objeto creado
        return $attachment;
    }

    public function download($filename)
    {
        // Ruta completa del archivo
        $filePath = storage_path('app/attachments/' . $filename);

        // Verifica si el archivo existe
        if (!Storage::exists('attachments/' . $filename)) {
            return response()->json(['error' => 'El archivo no existe.'], 404);
        }

        // Obtiene la extensión del archivo
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Determina el tipo de contenido adecuado según la extensión del archivo
        $contentType = $this->getContentType($extension);

        // Devuelve el archivo como una descarga de archivo normal
        return response()->file($filePath, ['Content-Type' => $contentType]);
    }

    private function getContentType($extension)
    {
        switch ($extension) {
            case 'pdf':
                return 'application/pdf';
            case 'jpg':
                return 'image/jpeg';
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            default:
                return 'application/octet-stream'; // Tipo de contenido genérico
        }
    }

    public function deleteAttachment(string $id)
    {
        // Obtiene attachment por su ID
        $attachment = Attachment::findOrFail($id);

        // Verifica si el usuario autenticado es el propietario del attachment
        if ($attachment->task->user_id !== Auth::id()) {
            // Si el usuario no es el propietario devuelve un 403 Forbidden
            return response()->json(['error' => 'No tienes permiso para eliminar este archivo adjunto.'], 403);
        }

        // TODO: Elimina el attachment del sistema de archivos
        Storage::delete('attachments/' . $attachment->path);

        // Eliminar el attachment(ruta) de la base de datos
        $attachment->delete();

        // Devolver una respuesta exitosa
        return response()->json(['message' => 'El archivo adjunto ha sido eliminado exitosamente.'], 200);
    }
}
