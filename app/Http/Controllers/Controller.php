<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Fut Match API",
 *     version="1.0.0",
 *     description="API para o sistema Fut Match - Plataforma de gerenciamento de partidas de futebol",
 *     @OA\Contact(
 *         email="contato@futmatch.com"
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Token de autenticação do Laravel Sanctum. Use: Bearer {token}"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor de desenvolvimento"
 * )
 *
 * @OA\Server(
 *     url="https://back-end-fut-match-9tjhhx.laravel.cloud",
 *     description="Servidor de produção"
 * )
 */
abstract class Controller
{
    //
}
