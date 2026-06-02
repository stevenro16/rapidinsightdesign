<?php
/**
 * SQLite → MySQL export script
 * Run: php sqlite_to_mysql.php > mysql.sql
 */

$sqliteFile = __DIR__ . '/database/database.sqlite';

if (!file_exists($sqliteFile)) {
    fwrite(STDERR, "Error: SQLite database not found at {$sqliteFile}\n");
    exit(1);
}

$pdo = new PDO("sqlite:{$sqliteFile}");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tables to skip (Laravel internals that MySQL re-creates fresh)
$skip = ['sqlite_sequence'];

// ── Column type map: SQLite affinity → MySQL DDL ───────────────────────────
// Keyed by table.column for precise overrides, then by generic type fallback.
$typeOverrides = [
    // users
    'users.id'                           => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'users.role'                         => "enum('admin','staff','customer') NOT NULL DEFAULT 'customer'",
    'users.name'                         => 'varchar(255) NOT NULL',
    'users.email'                        => 'varchar(255) NOT NULL',
    'users.email_verified_at'            => 'timestamp NULL DEFAULT NULL',
    'users.password'                     => 'varchar(255) NOT NULL',
    'users.company'                      => 'varchar(100) DEFAULT NULL',
    'users.notes'                        => 'text DEFAULT NULL',
    'users.remember_token'               => 'varchar(100) DEFAULT NULL',
    'users.created_at'                   => 'timestamp NULL DEFAULT NULL',
    'users.updated_at'                   => 'timestamp NULL DEFAULT NULL',
    // password_reset_tokens
    'password_reset_tokens.email'        => 'varchar(255) NOT NULL',
    'password_reset_tokens.token'        => 'varchar(255) NOT NULL',
    'password_reset_tokens.created_at'   => 'timestamp NULL DEFAULT NULL',
    // sessions
    'sessions.id'                        => 'varchar(255) NOT NULL',
    'sessions.user_id'                   => 'bigint unsigned DEFAULT NULL',
    'sessions.ip_address'                => 'varchar(45) DEFAULT NULL',
    'sessions.user_agent'                => 'text DEFAULT NULL',
    'sessions.payload'                   => 'longtext NOT NULL',
    'sessions.last_activity'             => 'int NOT NULL',
    // cache
    'cache.key'                          => 'varchar(255) NOT NULL',
    'cache.value'                        => 'mediumtext NOT NULL',
    'cache.expiration'                   => 'int NOT NULL',
    'cache_locks.key'                    => 'varchar(255) NOT NULL',
    'cache_locks.owner'                  => 'varchar(255) NOT NULL',
    'cache_locks.expiration'             => 'int NOT NULL',
    // jobs
    'jobs.id'                            => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'jobs.queue'                         => 'varchar(255) NOT NULL',
    'jobs.payload'                       => 'longtext NOT NULL',
    'jobs.attempts'                      => 'tinyint unsigned NOT NULL',
    'jobs.reserved_at'                   => 'int unsigned DEFAULT NULL',
    'jobs.available_at'                  => 'int unsigned NOT NULL',
    'jobs.created_at'                    => 'int unsigned NOT NULL',
    // job_batches
    'job_batches.id'                     => 'varchar(255) NOT NULL',
    'job_batches.name'                   => 'varchar(255) NOT NULL',
    'job_batches.total_jobs'             => 'int NOT NULL',
    'job_batches.pending_jobs'           => 'int NOT NULL',
    'job_batches.failed_jobs'            => 'int NOT NULL',
    'job_batches.failed_job_ids'         => 'longtext NOT NULL',
    'job_batches.options'                => 'mediumtext DEFAULT NULL',
    'job_batches.cancelled_at'           => 'int DEFAULT NULL',
    'job_batches.created_at'             => 'int NOT NULL',
    'job_batches.finished_at'            => 'int DEFAULT NULL',
    // failed_jobs
    'failed_jobs.id'                     => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'failed_jobs.uuid'                   => 'varchar(255) NOT NULL',
    'failed_jobs.connection'             => 'text NOT NULL',
    'failed_jobs.queue'                  => 'text NOT NULL',
    'failed_jobs.payload'                => 'longtext NOT NULL',
    'failed_jobs.exception'              => 'longtext NOT NULL',
    'failed_jobs.failed_at'              => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    // showroom_items
    'showroom_items.id'                  => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'showroom_items.title'               => 'varchar(100) NOT NULL',
    'showroom_items.description'         => 'text DEFAULT NULL',
    'showroom_items.embed_url'           => 'varchar(255) NOT NULL',
    'showroom_items.thumbnail_path'      => 'varchar(255) DEFAULT NULL',
    'showroom_items.tech_tags'           => 'varchar(200) DEFAULT NULL',
    'showroom_items.is_active'           => 'tinyint(1) NOT NULL DEFAULT 1',
    'showroom_items.sort_order'          => 'int unsigned NOT NULL DEFAULT 0',
    'showroom_items.created_at'          => 'timestamp NULL DEFAULT NULL',
    'showroom_items.updated_at'          => 'timestamp NULL DEFAULT NULL',
    // customer_showroom_access
    'customer_showroom_access.id'              => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'customer_showroom_access.user_id'         => 'bigint unsigned NOT NULL',
    'customer_showroom_access.showroom_item_id'=> 'bigint unsigned NOT NULL',
    'customer_showroom_access.granted_by'      => 'bigint unsigned NOT NULL',
    'customer_showroom_access.granted_at'      => 'timestamp NOT NULL',
    'customer_showroom_access.created_at'      => 'timestamp NULL DEFAULT NULL',
    'customer_showroom_access.updated_at'      => 'timestamp NULL DEFAULT NULL',
    // inquiries
    'inquiries.id'                       => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'inquiries.user_id'                  => 'bigint unsigned DEFAULT NULL',
    'inquiries.name'                     => 'varchar(100) NOT NULL',
    'inquiries.email'                    => 'varchar(255) NOT NULL',
    'inquiries.subject'                  => 'varchar(200) NOT NULL',
    'inquiries.message'                  => 'text NOT NULL',
    'inquiries.status'                   => "enum('new','in_progress','resolved') NOT NULL DEFAULT 'new'",
    'inquiries.created_at'               => 'timestamp NULL DEFAULT NULL',
    'inquiries.updated_at'               => 'timestamp NULL DEFAULT NULL',
    // site_contents
    'site_contents.id'                   => 'bigint unsigned NOT NULL AUTO_INCREMENT',
    'site_contents.key'                  => 'varchar(255) NOT NULL',
    'site_contents.value'                => 'text NOT NULL',
    'site_contents.created_at'           => 'timestamp NULL DEFAULT NULL',
    'site_contents.updated_at'           => 'timestamp NULL DEFAULT NULL',
    // migrations
    'migrations.id'                      => 'int unsigned NOT NULL AUTO_INCREMENT',
    'migrations.migration'               => 'varchar(255) NOT NULL',
    'migrations.batch'                   => 'int NOT NULL',
];

// Primary keys per table
$primaryKeys = [
    'users'                    => 'id',
    'sessions'                 => 'id',
    'cache'                    => 'key',
    'cache_locks'              => 'key',
    'jobs'                     => 'id',
    'job_batches'              => 'id',
    'failed_jobs'              => 'id',
    'showroom_items'           => 'id',
    'customer_showroom_access' => 'id',
    'inquiries'                => 'id',
    'site_contents'            => 'id',
    'migrations'               => 'id',
    'password_reset_tokens'    => 'email',
];

// Unique keys per table
$uniqueKeys = [
    'users'                    => [['email']],
    'failed_jobs'              => [['uuid']],
    'customer_showroom_access' => [['user_id', 'showroom_item_id']],
    'site_contents'            => [['key']],
];

// Index keys per table
$indexKeys = [
    'sessions' => [['user_id'], ['last_activity']],
    'jobs'     => [['queue']],
];

// Foreign keys per table
$foreignKeys = [
    'customer_showroom_access' => [
        'fk_csa_user'     => ['user_id',          'users', 'id', 'CASCADE', 'CASCADE'],
        'fk_csa_item'     => ['showroom_item_id',  'showroom_items', 'id', 'CASCADE', 'CASCADE'],
        'fk_csa_granted'  => ['granted_by',        'users', 'id', 'CASCADE', 'CASCADE'],
    ],
    'inquiries' => [
        'fk_inquiries_user' => ['user_id', 'users', 'id', 'SET NULL', 'CASCADE'],
    ],
];

// ── Helper: escape a value for MySQL INSERT ────────────────────────────────
function mysqlEscape($value): string {
    if ($value === null) return 'NULL';
    if (is_int($value) || is_float($value)) return (string)$value;
    $v = str_replace(
        ['\\',  "\0", "\n", "\r", "'",  '"',  "\x1a"],
        ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"',  '\\Z'],
        (string)$value
    );
    return "'{$v}'";
}

// ── Get all tables ─────────────────────────────────────────────────────────
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
              ->fetchAll(PDO::FETCH_COLUMN);

// ── Output header ──────────────────────────────────────────────────────────
echo "-- ============================================================\n";
echo "-- RapidInsight Designs — MySQL export\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- Source: SQLite → MySQL conversion\n";
echo "-- ============================================================\n\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n";
echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
echo "SET time_zone = '+00:00';\n\n";

foreach ($tables as $table) {
    if (in_array($table, $skip)) continue;

    // Get column info from SQLite PRAGMA
    $cols = $pdo->query("PRAGMA table_info(`{$table}`)")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cols)) continue;

    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

    echo "-- ------------------------------------------------------------\n";
    echo "-- Table: `{$table}`\n";
    echo "-- ------------------------------------------------------------\n";
    echo "DROP TABLE IF EXISTS `{$table}`;\n";
    echo "CREATE TABLE `{$table}` (\n";

    $colLines = [];
    foreach ($cols as $col) {
        $colName = $col['name'];
        $key     = "{$table}.{$colName}";
        $type    = $typeOverrides[$key] ?? 'varchar(255) DEFAULT NULL';
        $colLines[] = "  `{$colName}` {$type}";
    }

    // Primary key
    if (isset($primaryKeys[$table])) {
        $pk = $primaryKeys[$table];
        if (str_contains($pk, ',')) {
            $parts = array_map(fn($p) => "`".trim($p)."`", explode(',', $pk));
            $colLines[] = "  PRIMARY KEY (" . implode(', ', $parts) . ")";
        } else {
            $colLines[] = "  PRIMARY KEY (`{$pk}`)";
        }
    }

    // Unique keys
    foreach ($uniqueKeys[$table] ?? [] as $i => $ukCols) {
        $parts = array_map(fn($c) => "`{$c}`", $ukCols);
        $name  = 'uk_' . $table . '_' . implode('_', $ukCols);
        $colLines[] = "  UNIQUE KEY `{$name}` (" . implode(', ', $parts) . ")";
    }

    // Index keys
    foreach ($indexKeys[$table] ?? [] as $idxCols) {
        $parts = array_map(fn($c) => "`{$c}`", $idxCols);
        $name  = 'idx_' . $table . '_' . implode('_', $idxCols);
        $colLines[] = "  KEY `{$name}` (" . implode(', ', $parts) . ")";
    }

    // Foreign keys
    foreach ($foreignKeys[$table] ?? [] as $fkName => $fk) {
        [$col, $refTable, $refCol, $onDelete, $onUpdate] = $fk;
        $colLines[] = "  CONSTRAINT `{$fkName}` FOREIGN KEY (`{$col}`) REFERENCES `{$refTable}` (`{$refCol}`) ON DELETE {$onDelete} ON UPDATE {$onUpdate}";
    }

    echo implode(",\n", $colLines) . "\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    // Data
    if (!empty($rows)) {
        $colNames = array_map(fn($c) => "`{$c['name']}`", $cols);
        $chunkSize = 100;
        $chunks = array_chunk($rows, $chunkSize);

        foreach ($chunks as $chunk) {
            echo "INSERT INTO `{$table}` (" . implode(', ', $colNames) . ") VALUES\n";
            $valueLines = [];
            foreach ($chunk as $row) {
                $vals = array_map('mysqlEscape', array_values($row));
                $valueLines[] = "  (" . implode(', ', $vals) . ")";
            }
            echo implode(",\n", $valueLines) . ";\n";
        }
        echo "\n";
    }
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
echo "-- End of export\n";
