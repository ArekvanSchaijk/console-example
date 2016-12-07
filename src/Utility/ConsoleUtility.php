<?php
namespace AlterNET\Cli\Utility;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class ConsoleUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ConsoleUtility
{

    /**
     * Git Clone
     *
     * @param string $url
     * @param string $destination
     * @static
     */
    static public function gitClone($url, $destination)
    {
        $process = new Process('rm -rf ' . $destination . ';git clone ' . $url . ' ' . $destination);
        $process->run();
        self::unSuccessfulProcessExceptionHandler($process);
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
     * Composer Install
     *
     * @param string $directory
     * @return void
     * @static
     */
    static public function composerInstall($directory)
    {
        $process = new Process(($directory ? 'cd ' . $directory . ';' : null) . 'composer install');
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

}