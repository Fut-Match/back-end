<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HealthController extends Controller
{
    public function status()
    {
        return response()->json([
            'status' => 'active',
            'timestamp' => now(),
        ], Response::HTTP_OK);
    }
}
