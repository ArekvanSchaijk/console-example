<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\Config;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class ConsoleUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ConsoleUtility
{

    /**
     * Gets the Config
     *
     * @return Config
     * @static
     */
    static public function getConfig()
    {
        return Config::create();
    }

    /**
     * Git Checkout
     *
     * @param string $branch
     * @param string|null $directory
     * @static
     */
    static public function gitCheckout($branch, $directory = null)
    {
        $process = new Process(($directory ? 'cd ' . $directory . ';' : null) . 'git checkout ' . $branch);
        $process->run();
        self::unSuccessfulProcessExceptionHandler($process);
    }

    /**
     * Un Successful Process Exception Handler
     *
     * @param Process $process
     * @static
     */
    static public function unSuccessfulProcessExceptionHandler(Process $process)
    {
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Is Internet Connection
     * Checks if there is an internet connection (or not)
     *
     * @return bool
     * @static
     */
    static public function isInternetConnection()
    {
        return (bool)@fsockopen('www.google.com', 80, $num, $error, 5);
    }

    /**
     * File System
     *
     * @return Filesystem
     * @static
     */
    static public function fileSystem()
    {
        if (!isset($GLOBALS['ALTERNET_CLI_FILESYSTEM']) || !$GLOBALS['ALTERNET_CLI_FILESYSTEM'] instanceof Filesystem) {
            $GLOBALS['ALTERNET_CLI_FILESYSTEM'] = new Filesystem();
        }
        return $GLOBALS['ALTERNET_CLI_FILESYSTEM'];
    }

}