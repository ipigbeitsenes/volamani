<?php

return [
    /*
    | Filesystem disk the compressed dumps are written to. For real disaster
    | recovery this MUST be off-box — point BACKUP_DISK at an S3 bucket in
    | production so a lost server never means a lost database.
    */
    'disk' => env('BACKUP_DISK', 'local'),

    // Folder within the disk to hold the dumps.
    'path' => env('BACKUP_PATH', 'backups'),

    // Delete dumps older than this many days on each run (0 = keep forever).
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
];
