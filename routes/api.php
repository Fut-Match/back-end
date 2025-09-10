<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\UserController;
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
    Route::post('logout-all', [AuthController::class, 'logoutAll']);
    Route::get('user', [AuthController::class, 'user']);

    // Rotas de usuários protegidas
    Route::prefix('users')->group(function () {
        Route::get('me', [UserController::class, 'me']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::delete('{user}', [UserController::class, 'destroy']);
    });

    // Rotas de jogadores protegidas
    Route::prefix('players')->group(function () {
        Route::get('me', [PlayerController::class, 'me']);
        Route::put('{player}', [PlayerController::class, 'update']);
    });

    // Rotas de partidas protegidas
    Route::prefix('matches')->group(function () {
        Route::get('/', [MatchController::class, 'index']);
        Route::post('/', [MatchController::class, 'store']);
        Route::get('{match}', [MatchController::class, 'show']);
        Route::put('{match}', [MatchController::class, 'update']);
        Route::delete('{match}', [MatchController::class, 'destroy']);
        Route::post('join', [MatchController::class, 'join']);
        Route::post('{match}/leave', [MatchController::class, 'leave']);
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
