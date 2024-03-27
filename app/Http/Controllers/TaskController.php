<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use Barryvdh\DomPDF\Facade as PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display tasks of the authenticated user.
     */
    public function userTasks()
    {
        $user = Auth::user();
        $tasks = $user->tasks()->with('status:id,status', 'user:id,name')->get();
        return $tasks;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('status:id,status', 'user:id,name')->get();
        return $tasks;
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
        // Recupera la task con sus attachments y comments
        $task = Task::with(['status:id,status','user:id,name','attachments.user', 'comments.user'])->findOrFail($id);

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
        // Recupera el user autenticado y la task a editar
        $user = Auth::user();
        $task = Task::findOrFail($id);

        // Verifica que la task eprtenezca al user o que este sea super_admin
        if ($task->user_id !== $user->id && $user->role !== 'super_admin') {
            // Si la tarea no pertenece al usuario, devolver un error 403 Forbidden
            return response()->json(['error' => 'No tienes permiso para actualizar el estado de esta tarea.'], 403);
        }

        // Actualiza el estado de la task
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
        // Filtra tareas por fecha
        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('start_date'))->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('end_date'))->endOfDay();

        $tasks = Task::whereBetween('completed_at', [$startDate, $endDate])->get();

        // Formatea las fechas de las tareas para el PDF
        $tasks->transform(function ($task) {
            $task->completed_at = Carbon::parse($task->completed_at)->format('d-m-y'); // Formato DD-MM-YY
            return $task;
        });

        // Formatea las fechas para el título
        $formattedStartDate = $startDate->format('d-m-Y'); // Formato DD-MM-YYYY
        $formattedEndDate = $endDate->format('d-m-Y'); // Formato DD-MM-YYYY

        // Prepara datos para el PDF
        $data = [
            'tasks' => $tasks,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
        ];

        // Genera el PDF a partir de la vista Blade
        $pdf = \PDF::loadView('pdf.tasks', $data);

        // Descarga el PDF
        return $pdf->download('tasks_report.pdf');
    }

}
