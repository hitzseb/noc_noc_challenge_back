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
        // Validar la solicitud de carga de archivos
        $validator = Validator::make($request->all(), [
            'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Verificar si la tarea existe
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['error' => 'La tarea especificada no existe'], 404);
        }

        // Obtener el archivo cargado
        $file = $request->file('attachment');

        // Guardar el archivo en el sistema de archivos de tu aplicación
        $filePath = $file->store('attachments');

        // Crear un nuevo registro de adjunto y guardar la ruta del archivo
        $attachment = new Attachment();
        $attachment->path = basename($filePath);
        $attachment->task_id = $id;
        $attachment->save();

        // Retornar una respuesta con el adjunto creado
        return $attachment;
    }

    public function download($filename)
    {
        // Obtener la ruta completa del archivo
        $filePath = storage_path('app/attachments/' . $filename);

        // Verificar si el archivo existe
        if (!Storage::exists('attachments/' . $filename)) {
            return response()->json(['error' => 'El archivo no existe.'], 404);
        }

        // Obtener la extensión del archivo
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Determinar el tipo de contenido adecuado según la extensión del archivo
        $contentType = $this->getContentType($extension);

        // Devolver el archivo como una descarga de archivo normal
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
                return 'application/octet-stream'; // Tipo de contenido genérico para otros tipos de archivo
        }
    }

    public function deleteAttachment(string $id)
    {
        // Obtener el archivo adjunto por su ID
        $attachment = Attachment::findOrFail($id);

        // Verificar si el usuario autenticado es el propietario del archivo adjunto
        if ($attachment->task->user_id !== Auth::id()) {
            // Si el usuario no es el propietario del archivo adjunto, devolver un error 403 Forbidden
            return response()->json(['error' => 'No tienes permiso para eliminar este archivo adjunto.'], 403);
        }

        // Eliminar el archivo adjunto del sistema de archivos
        Storage::delete('attachments/' . $attachment->path);

        // Eliminar el archivo adjunto de la base de datos
        $attachment->delete();

        // Devolver una respuesta exitosa
        return response()->json(['message' => 'El archivo adjunto ha sido eliminado exitosamente.'], 200);
    }
}
