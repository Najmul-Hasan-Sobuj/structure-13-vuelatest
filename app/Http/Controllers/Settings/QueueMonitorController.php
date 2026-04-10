<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Queue\Failed\CountableFailedJobProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class QueueMonitorController extends Controller
{
    /**
     * Show queue connection health for the default queue connection.
     */
    public function edit(): Response
    {
        $connectionName = config('queue.default');
        $connectionConfig = config("queue.connections.{$connectionName}", []);
        $driver = $connectionConfig['driver'] ?? 'unknown';

        $pendingSize = Queue::connection()->size();

        $failedCount = null;
        $failer = app('queue.failer');
        if ($failer instanceof CountableFailedJobProvider) {
            $failedCount = $failer->count();
        }

        $reservedCount = null;
        if ($driver === 'database') {
            $table = $connectionConfig['table'] ?? 'jobs';
            $dbConnectionName = $connectionConfig['connection'] ?? null;
            $queueName = $connectionConfig['queue'] ?? 'default';

            $reservedCount = DB::connection($dbConnectionName)
                ->table($table)
                ->where('queue', $queueName)
                ->whereNotNull('reserved_at')
                ->count();
        }

        $batchSummary = $this->batchSummary();

        return Inertia::render('settings/Queue', [
            'connectionName' => $connectionName,
            'driver' => $driver,
            'pendingSize' => $pendingSize,
            'failedCount' => $failedCount,
            'reservedCount' => $reservedCount,
            'batchSummary' => $batchSummary,
        ]);
    }

    /**
     * @return array{active_batches: int, pending_jobs: int, failed_jobs: int}|null
     */
    protected function batchSummary(): ?array
    {
        $batchingConnection = config('queue.batching.database');
        $batchTable = config('queue.batching.table', 'job_batches');

        if (! $batchingConnection || ! Schema::connection($batchingConnection)->hasTable($batchTable)) {
            return null;
        }

        $row = DB::connection($batchingConnection)
            ->table($batchTable)
            ->whereNull('finished_at')
            ->selectRaw('count(*) as active_batches, coalesce(sum(pending_jobs), 0) as pending_jobs, coalesce(sum(failed_jobs), 0) as failed_jobs')
            ->first();

        if ($row === null) {
            return [
                'active_batches' => 0,
                'pending_jobs' => 0,
                'failed_jobs' => 0,
            ];
        }

        return [
            'active_batches' => (int) $row->active_batches,
            'pending_jobs' => (int) $row->pending_jobs,
            'failed_jobs' => (int) $row->failed_jobs,
        ];
    }
}
