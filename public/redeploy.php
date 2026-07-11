<?php

declare(strict_types=1);

set_time_limit(0);
ini_set('memory_limit', '512M');
ini_set('output_buffering', '0');
ini_set('zlib.output_compression', '0');

if (PHP_SAPI !== 'cli') {
    $password = (string) ($_SERVER['PHP_AUTH_PW'] ?? '');

    if (!hash_equals('1234', $password)) {
        header('WWW-Authenticate: Basic realm="Snovi redeploy"');
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: text/plain; charset=utf-8');
        echo "Authentication required.\n";
        exit;
    }
}

$baseDir = dirname(__DIR__);
chdir($baseDir);
putenv('COMPOSER_ALLOW_SUPERUSER=1');
putenv('COMPOSER_NO_INTERACTION=1');

$composerHome = $baseDir.DIRECTORY_SEPARATOR.'.composer';
$composerCache = $composerHome.DIRECTORY_SEPARATOR.'cache';

foreach ([$composerHome, $composerCache] as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

putenv('HOME='.$composerHome);
putenv('COMPOSER_HOME='.$composerHome);
putenv('COMPOSER_CACHE_DIR='.$composerCache);

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
}

while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

$startedAt = time();
$write = static function (string $message): void {
    echo $message;
    flush();
};
$shellArg = static fn (string $value): string => escapeshellarg($value);

$findExecutable = static function (array $commands): ?string {
    foreach ($commands as $command) {
        if (str_contains($command, DIRECTORY_SEPARATOR) && is_file($command) && is_executable($command)) {
            return $command;
        }

        $check = PHP_OS_FAMILY === 'Windows'
            ? 'where '.escapeshellarg($command)
            : 'command -v '.escapeshellarg($command);
        $output = [];
        exec($check, $output, $exitCode);
        if ($exitCode === 0 && isset($output[0]) && $output[0] !== '') {
            return trim($output[0]);
        }
    }

    return null;
};

$php = $findExecutable(array_filter([
    PHP_SAPI === 'cli' ? PHP_BINARY : null,
    'php',
    '/usr/local/bin/php',
    '/usr/bin/php',
    '/opt/cpanel/ea-php82/root/usr/bin/php',
    '/opt/cpanel/ea-php83/root/usr/bin/php',
    '/opt/cpanel/ea-php84/root/usr/bin/php',
]));

if ($php === null) {
    $write("Could not find CLI PHP.\n");
    exit(127);
}

$phpCommand = $shellArg($php);
$composerPhar = $baseDir.DIRECTORY_SEPARATOR.'composer.phar';
$composer = is_file($composerPhar)
    ? $phpCommand.' '.$shellArg($composerPhar)
    : $findExecutable(['composer']);

if ($composer === null) {
    $write("Composer was not found. Install it globally or place composer.phar in the project root.\n");
    exit(127);
}

$runCommand = static function (string $command, string $cwd, callable $write): int {
    $process = proc_open($command, [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes, $cwd);

    if (!is_resource($process)) {
        $write("Failed to start command: {$command}\n");
        return 1;
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $lastOutputAt = time();
    $exitCode = null;

    do {
        foreach ([1, 2] as $pipeNumber) {
            while (($line = fgets($pipes[$pipeNumber])) !== false) {
                $lastOutputAt = time();
                $write($line);
            }
        }

        $status = proc_get_status($process);
        if (!$status['running']) {
            $exitCode = $status['exitcode'];
            break;
        }

        if (time() - $lastOutputAt >= 15) {
            $lastOutputAt = time();
            $write('... still running at '.date('H:i:s')."\n");
        }
        usleep(100000);
    } while (true);

    foreach ([1, 2] as $pipeNumber) {
        while (($line = fgets($pipes[$pipeNumber])) !== false) {
            $write($line);
        }
        fclose($pipes[$pipeNumber]);
    }

    $closeCode = proc_close($process);
    return is_int($exitCode) && $exitCode >= 0 ? $exitCode : $closeCode;
};

$recordCompletedMigrations = static function (string $baseDir, callable $write): int {
    require_once $baseDir.'/vendor/autoload.php';
    $app = require $baseDir.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
        \Illuminate\Support\Facades\Schema::create('migrations', function (\Illuminate\Database\Schema\Blueprint $table): void {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
    }

    // These migrations were applied manually on the existing production database.
    // The explicit list ensures that future migrations still execute normally.
    $completedMigrations = [
        '0001_01_01_000000_create_users_table',
        '0001_01_01_000001_create_cache_table',
        '0001_01_01_000002_create_jobs_table',
        '2026_02_09_125603_create_categories_table',
        '2026_02_09_125607_create_subcategories_table',
        '2026_02_09_125613_create_stories_table',
        '2026_02_09_141757_update_categories_and_subcategories_slug_and_drop_fields',
        '2026_02_09_145745_add_api_token_to_users_table',
        '2026_02_09_170300_add_username_to_users_table',
        '2026_03_12_000001_create_music_table',
        '2026_03_12_000002_add_music_id_to_stories_table',
        '2026_03_13_000003_add_sort_to_categories_and_subcategories_table',
        '2026_05_29_000001_add_music_level_to_stories_table',
        '2026_06_09_000001_create_push_tokens_table',
        '2026_06_09_000002_create_push_notifications_table',
        '2026_06_16_000001_create_gift_codes_table',
        '2026_07_08_000001_add_expires_at_to_gift_codes_table',
        '2026_07_09_000001_add_email_to_gift_codes_table',
        '2026_07_11_000001_add_can_seek_to_stories_table',
    ];

    $recorded = \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration')->all();
    $missing = array_values(array_diff($completedMigrations, $recorded));

    if ($missing === []) {
        $write("Migration baseline already complete.\n");
        return 0;
    }

    $batch = ((int) \Illuminate\Support\Facades\DB::table('migrations')->max('batch')) + 1;
    $rows = array_map(
        static fn (string $migration): array => ['migration' => $migration, 'batch' => $batch],
        $missing,
    );
    \Illuminate\Support\Facades\DB::table('migrations')->insert($rows);
    $write('Recorded '.count($missing)." manually completed migrations in batch {$batch}.\n");

    return 0;
};

$commands = [
    ['label' => 'Pulling latest code', 'command' => 'git pull --ff-only'],
    ['label' => 'Installing Composer dependencies', 'command' => $composer.' install --no-interaction --prefer-dist --no-progress --optimize-autoloader --no-dev --no-ansi'],
    ['label' => 'Clearing Laravel config cache', 'command' => $phpCommand.' artisan config:clear --no-ansi'],
    ['label' => 'Clearing Laravel application cache', 'command' => $phpCommand.' artisan cache:clear --no-ansi'],
    ['label' => 'Clearing Laravel route cache', 'command' => $phpCommand.' artisan route:clear --no-ansi'],
    ['label' => 'Clearing Laravel view cache', 'command' => $phpCommand.' artisan view:clear --no-ansi'],
    ['label' => 'Recording manually completed migrations', 'action' => $recordCompletedMigrations],
    ['label' => 'Running database migrations', 'command' => $phpCommand.' artisan migrate --force --no-ansi'],
];

foreach ($commands as $step) {
    $write("\n=== {$step['label']} ===\n");
    if (isset($step['action'])) {
        $exitCode = $step['action']($baseDir, $write);
    } else {
        $write("Command: {$step['command']}\n");
        $exitCode = $runCommand($step['command'], $baseDir, $write);
    }
    if ($exitCode !== 0) {
        $write("{$step['label']} failed with exit code {$exitCode}.\n");
        exit($exitCode);
    }
}

$write("\nRedeploy completed successfully in ".(time() - $startedAt)."s.\n");
