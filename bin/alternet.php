#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('log_errors', 0);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    // We are globally installed via Composer
    require __DIR__ . '/../../../autoload.php';
} else {
    echo "Composer autoload file not found.\n";
    echo "You need to run 'composer install'.\n";
    exit(1);
}

(new \AlterNET\Cli\AlternetConsole())->run();