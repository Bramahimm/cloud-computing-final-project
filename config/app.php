<?php
// FILE: config/app.php

// 1. Session Start (HARUS dipanggil sebelum output apapun)
session_start();

// 2. Constants
define('BASE_URL', '/');
define('APP_NAME', 'Web Absensi Smart');
define('DEFAULT_PASSWORD', '123'); // Untuk fitur reset

// 3. Load Helpers
require_once __DIR__ . '/../helpers/functions.php';

// 4. Load Database Connection
require_once __DIR__ . '/database.php';

// 5. Load Models (autoloader sederhana)
spl_autoload_register(function ($class) {
  $file = __DIR__ . '/../models/' . $class . '.php';
  if (file_exists($file)) {
    require_once $file;
  }
});

// 6. Global PDO Instance
$pdo = getDBConnection();
