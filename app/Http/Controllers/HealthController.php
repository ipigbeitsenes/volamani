<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Deep health probe for load balancers and uptime monitors.
 *
 * Laravel's built-in /up only proves the framework booted; it says nothing about
 * the datastores the app actually needs. This checks the live dependencies and
 * returns 503 when any hard dependency is down so traffic is routed away.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::connection()->getPdo() !== null),
            'cache' => $this->check(function () {
                Cache::put('health:ping', '1', 5);

                return Cache::get('health:ping') === '1';
            }),
        ];

        // Backlog of dead-lettered jobs is a warning signal, not a hard failure.
        $failedJobs = $this->safeCount('failed_jobs');

        $healthy = ! collect($checks)->contains(fn ($c) => $c['status'] !== 'ok');

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'failed_jobs' => $failedJobs,
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function check(callable $probe): array
    {
        try {
            return ['status' => $probe() ? 'ok' : 'fail'];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'error' => class_basename($e)];
        }
    }

    private function safeCount(string $table): ?int
    {
        try {
            return DB::table($table)->count();
        } catch (\Throwable) {
            return null;
        }
    }
}
