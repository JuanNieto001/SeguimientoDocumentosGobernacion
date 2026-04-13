<?php

return [
    'availability' => [
        'target_percentage' => (float) env('OPS_TARGET_AVAILABILITY', 95),
        'rto_minutes' => (int) env('OPS_RTO_MINUTES', 120),
    ],

    'concurrency' => [
        'target_active_users' => (int) env('OPS_TARGET_CONCURRENT_USERS', 100),
    ],

    'backup' => [
        'path' => env('BACKUP_TARGET_PATH', storage_path('backups')),
        'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
        'files_source_path' => env('BACKUP_FILES_SOURCE_PATH', storage_path('app/public/procesos')),
        'mysql_dump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'mysql_binary' => env('MYSQL_BINARY', 'mysql'),
    ],
];
