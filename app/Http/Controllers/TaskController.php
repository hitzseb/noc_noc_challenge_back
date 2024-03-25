<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use Barryvdh\DomPDF\Facade as PDF;
use Dompdf\Dompdf;
use Dompdf\Options;


class TaskController extends Controller
{
    /**
     * Display tasks of the authenticated user.
     */
    public function userTasks()
    {
        $user = Auth::user();
        $tasks = $user->tasks()->get();
        return $tasks;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Task::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return Task::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Recuperar la tarea con sus archivos adjuntos cargados
        $task = Task::with(['attachments', 'comments'])->findOrFail($id);

        return $task;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);
        $task->update($request->all());
        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, string $id)
    {
        // Verificar si el usuario autenticado es un super admin
        $user = Auth::user();

        // Verificar si la tarea pertenece al usuario
        $task = Task::findOrFail($id);
        if ($task->user_id !== $user->id) {
            // Si la tarea no pertenece al usuario, devolver un error 403 Forbidden
            return response()->json(['error' => 'No tienes permiso para actualizar el estado de esta tarea.'], 403);
        }

        // Actualizar el estado de la tarea
        $task->update(['status_id' => $request->status_id]);

        // Si el nuevo estado es "completado", actualiza la fecha de completado
        if ($request->status_id == 4) {
            $task->update(['completed_at' => now()]);
        }

        // Devolver la tarea actualizada
        return $task;
    }

    public function generateReport(Request $request)
    {
        // Filtrar tareas por fecha
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $tasks = Task::whereBetween('completed_at', [$startDate, $endDate])->get();

        // Preparar datos para el PDF
        $data = [
            'tasks' => $tasks,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        // Generar el PDF a partir de la vista Blade
        $pdf = \PDF::loadView('pdf.tasks', $data);

        // Descargar el PDF
        return $pdf->download('tasks_report.pdf');
    }
}
