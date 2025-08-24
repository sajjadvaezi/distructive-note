<?php
// Database configuration
define('DB_DSN', getenv('DB_DSN') ?: 'mysql:host=localhost;port=3306;dbname=destruct;charset=utf8mb4');
define('DB_USER', getenv('DB_USER') ?: 'app');
define('DB_PASS', getenv('DB_PASS') ?: 'apppass');

// Application settings
define('SITE_NAME', 'Distruct Note');
define('SITE_URL', 'http://localhost:8080');
define('DEFAULT_MAX_VIEWS', 1);
define('MAX_VIEWS_LIMIT', 100);
define('NOTE_EXPIRY_DAYS', 7);

// Security settings
define('ID_LENGTH', 32);
define('SALT_ROUNDS', 12);
