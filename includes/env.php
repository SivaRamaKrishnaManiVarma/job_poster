<?php
/**
 * Lightweight .env File Loader
 *
 * Reads KEY=VALUE pairs from a .env file and registers them as environment
 * variables. Supports single/double-quoted values and inline comments (#).
 *
 * Usage:
 *   require_once __DIR__ . '/env.php';
 *   loadEnvFile(__DIR__ . '/../.env');
 *   $val = getenv('DB_NAME');
 *
 * Rules:
 *   - System environment variables always take priority (not overwritten).
 *   - Missing .env file is silently ignored — fallback logic in config.php runs.
 *   - Malformed lines (no '=') are skipped without error.
 */
function loadEnvFile(string $filePath): void
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return; // Silently degrade — config.php has hardcoded fallbacks
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and blank lines
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Must contain an '=' to be a valid assignment
        if (!str_contains($line, '=')) {
            continue;
        }

        // Split on first '=' only (values may contain '=' themselves)
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Strip surrounding quotes (single or double)
        if (
            strlen($value) >= 2 &&
            (
                (str_starts_with($value, '"')  && str_ends_with($value, '"'))  ||
                (str_starts_with($value, "'")  && str_ends_with($value, "'"))
            )
        ) {
            $value = substr($value, 1, -1);
        }

        // Strip inline comments (e.g. VAR=value # comment)
        if (str_contains($value, ' #')) {
            $value = trim(explode(' #', $value, 2)[0]);
        }

        // Key must be a valid identifier
        if ($key === '' || !preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key)) {
            continue;
        }

        // System env vars take priority — never overwrite them
        if (getenv($key) !== false || isset($_ENV[$key])) {
            continue;
        }

        putenv("$key=$value");
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
    }
}
