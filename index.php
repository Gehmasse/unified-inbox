<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use Micro\UnifiedInbox\App;

require __DIR__ . '/vendor/autoload.php';

echo new App()->response();
