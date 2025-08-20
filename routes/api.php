<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Rota de health check
Route::get('health', [HealthController::class, 'status']);

// Rotas públicas de autenticação
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail']);

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de autenticação que precisam de token
    Route::post('logout', [AuthController::class, 'logout']);

    // Rotas de jogadores protegidas
    Route::prefix('players')->group(function () {
        Route::get('me', [PlayerController::class, 'me']);
        Route::put('{player}', [PlayerController::class, 'update']);
    });

    // Outras rotas protegidas da API podem ser adicionadas aqui
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Rotas públicas de jogadores
Route::prefix('players')->group(function () {
    Route::get('/', [PlayerController::class, 'index']);
    Route::get('{player}', [PlayerController::class, 'show']);
});
