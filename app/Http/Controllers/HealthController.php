<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HealthController extends Controller
{

/**
 * @OA\Get(
 *     path="/api/health",
 *     summary="Verifica o status do projeto",
 *     tags={"Health"},
 *     @OA\Response(
 *         response=200,
 *         description="Status do projeto",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
    public function status()
    {
        return response()->json([
            'status' => 'active',
            'timestamp' => now(),
        ], Response::HTTP_OK);
    }
}
