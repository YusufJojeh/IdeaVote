<?php
// Tiny .env loader: parses KEY=VALUE lines into $_ENV if file exists

if (!function_exists('load_env')) {
    function load_env(string $path = __DIR__ . '/../.env'): void {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(ltrim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            $val = trim($val, "\"' ");
            if ($key !== '') {
                $_ENV[$key] = $val;
            }
        }
    }
}

load_env();

?>


