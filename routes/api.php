<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;

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

// Authentication routes (register, login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Materi routes
    Route::get('materi', [MateriController::class, 'index']);
    Route::get('materi/{id}', [MateriController::class, 'show']);
    Route::post('materi', [MateriController::class, 'store']);  // Hanya untuk admin

    // Question routes
    Route::get('materi/{materiId}/questions', [QuestionController::class, 'getByMateri']);
    Route::get('materi/{materiId}/questions/{level}', [QuestionController::class, 'getByMateriAndLevel']);
    Route::post('materi/{materiId}/submit-quiz', [QuestionController::class, 'submitQuiz']);
});