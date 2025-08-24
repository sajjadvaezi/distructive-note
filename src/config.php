<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue; // Skip comments
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Database configuration
define('DB_DSN', getenv('DB_DSN') ?: 'mysql:host=localhost;port=3306;dbname=destruct;charset=utf8mb4');
define('DB_USER', getenv('DB_USER') ?: 'app');
define('DB_PASS', getenv('DB_PASS') ?: 'apppass');

// Application settings
define('SITE_NAME', getenv('SITE_NAME') ?: 'Distruct Note');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost:8080');
define('DEFAULT_MAX_VIEWS', (int)(getenv('DEFAULT_MAX_VIEWS') ?: 1));
define('MAX_VIEWS_LIMIT', (int)(getenv('MAX_VIEWS_LIMIT') ?: 100));
define('NOTE_EXPIRY_DAYS', (int)(getenv('NOTE_EXPIRY_DAYS') ?: 7));

// Security settings
define('ID_LENGTH', (int)(getenv('ID_LENGTH') ?: 32));
define('SALT_ROUNDS', (int)(getenv('SALT_ROUNDS') ?: 12));
