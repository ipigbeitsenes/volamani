<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Compressed, off-box-capable MySQL backups.
 *
 * Streams a mysqldump straight to gzip, uploads it to the configured disk
 * (point BACKUP_DISK at S3 in production for real DR), then prunes dumps past
 * the retention window. Scheduled daily; can be run ad hoc before risky work.
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep= : Override retention days for this run}';

    protected $description = 'Dump the MySQL database, compress it, store it off-box, and prune old backups';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->warn("db:backup only supports MySQL; current connection is [{$connection}]. Skipping.");

            return self::SUCCESS;
        }

        $db = config("database.connections.{$connection}");
        $disk = config('backup.disk');
        $dir = trim(config('backup.path'), '/');

        $filename = sprintf('%s-%s.sql.gz', $db['database'], Carbon::now()->format('Y-m-d-His'));
        $tmp = tempnam(sys_get_temp_dir(), 'dbdump');

        // Password via MYSQL_PWD so it never appears in the process list / logs.
        $command = sprintf(
            'mysqldump --single-transaction --quick --no-tablespaces --host=%s --port=%s --user=%s %s | gzip > %s',
            escapeshellarg((string) $db['host']),
            escapeshellarg((string) $db['port']),
            escapeshellarg((string) $db['username']),
            escapeshellarg((string) $db['database']),
            escapeshellarg($tmp),
        );

        $this->info("Backing up [{$db['database']}] → {$disk}:{$dir}/{$filename}");

        $result = Process::timeout(600)
            ->env(['MYSQL_PWD' => (string) ($db['password'] ?? '')])
            ->run($command);

        if (! $result->successful()) {
            @unlink($tmp);
            $error = trim($result->errorOutput()) ?: 'mysqldump failed';
            Log::error("db:backup failed: {$error}");
            $this->error($error);

            return self::FAILURE;
        }

        $stream = fopen($tmp, 'r');
        Storage::disk($disk)->writeStream("{$dir}/{$filename}", $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($tmp);

        $size = Storage::disk($disk)->size("{$dir}/{$filename}");
        $this->info('Backup stored ('.number_format($size / 1024, 1).' KB).');

        $this->prune($disk, $dir);

        return self::SUCCESS;
    }

    /** Delete dumps older than the retention window. */
    private function prune(string $disk, string $dir): void
    {
        $keep = (int) ($this->option('keep') ?? config('backup.retention_days'));

        if ($keep <= 0) {
            return;
        }

        $cutoff = Carbon::now()->subDays($keep)->getTimestamp();
        $deleted = 0;

        foreach (Storage::disk($disk)->files($dir) as $file) {
            if (! str_ends_with($file, '.sql.gz')) {
                continue;
            }

            if (Storage::disk($disk)->lastModified($file) < $cutoff) {
                Storage::disk($disk)->delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Pruned {$deleted} backup(s) older than {$keep} day(s).");
        }
    }
}
