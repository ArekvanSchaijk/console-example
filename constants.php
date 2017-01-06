<?php
define('CLI_ROOT', __DIR__);
define('CLI_HOME', \AlterNET\Cli\Utility\GeneralUtility::getHomeDirectory() . '/.alternet_cli');
define('CLI_HOME_PRIVATE', CLI_HOME . '/.private');
define('CLI_HOME_BUILDS', CLI_HOME . '/builds');
define('CLI_HOME_CONFIG_FILE_PATH', CLI_HOME . '/config.yaml');
define('CLI_DEFAULT_HOME_CONFIG_FILE_PATH', CLI_ROOT . '/home.config.yaml');
define('CLI_DEFAULT_BACKUP_PATH', \AlterNET\Cli\Utility\GeneralUtility::getHomeDirectory() . '/alternet_backups');