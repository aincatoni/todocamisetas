<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'service' => 'TodoCamisetas API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200, ['Cache-Control' => 'public, max-age=15']);
    }
}
