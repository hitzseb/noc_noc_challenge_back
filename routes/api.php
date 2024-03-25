<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\AttachmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Rutas de jwt-auth
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

});

// ruta para que el super admin cree nuevos usuarios
Route::middleware('require_super_admin')->post('/create-user', [UserController::class, 'create']);

// rutas de reestablecimiento de passwords
Route::post('forgot-password', [PasswordController::class, 'forgotPassword']);
Route::post('update-password', [PasswordController::class, 'updatePassword']);

// Rutas de status
Route::get('/status', [StatusController::class, 'index']);

// Rutas para tasks
Route::middleware('require_super_admin')->post('/create-task', [TaskController::class, 'store']);
Route::middleware('auth')->get('/my-tasks', [TaskController::class, 'userTasks']);
Route::middleware('auth')->get('/all-tasks', [TaskController::class, 'index']);
Route::middleware('auth')->get('/tasks/{id}', [TaskController::class, 'show']);
Route::middleware('auth')->put('/tasks/{id}/update-status', [TaskController::class, 'updateStatus']);
Route::middleware('require_super_admin')->get('/generate-report', [TaskController::class, 'generateReport']);

// Ruta para la carga/descarga de archivos
Route::post('/tasks/{id}/attach', [AttachmentController::class, 'store']);
Route::get('/download/{filename}', [AttachmentController::class, 'download'])->name('attachment.download');
Route::middleware('auth')->delete('/attachments/{id}/delete', [AttachmentController::class, 'deleteAttachment']);
