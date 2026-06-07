<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $databaseHealthy = $this->databaseIsHealthy();

        return response()->json([
            'status' => $databaseHealthy ? 'ok' : 'degraded',
            'checks' => [
                'database' => $databaseHealthy ? 'ok' : 'unavailable',
            ],
            'app' => config('site.name'),
            'timestamp' => Carbon::now()->toIso8601String(),
        ], $databaseHealthy ? 200 : 503);
    }

    private function databaseIsHealthy(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
