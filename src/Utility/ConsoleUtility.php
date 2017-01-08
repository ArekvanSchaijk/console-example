<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\Config;
use AlterNET\Cli\Container\DataContainer;
use AlterNET\Cli\Local\Service\HostFileService;
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
     * @var Filesystem
     */
    static protected $fileSystem;

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
     * Gets the Filesystem
     *
     * @return Filesystem
     * @static
     */
    static public function getFileSystem()
    {
        if (is_null(self::$fileSystem)) {
            self::$fileSystem = new Filesystem();
        }
        return self::$fileSystem;
    }

    /**
     * Gets the Host File Service
     *
     * @return HostFileService
     * @static
     */
    static public function getHostFileService()
    {
        return HostFileService::create();
    }

    /**
     * Gets the Data Container
     *
     * @return DataContainer
     * @static
     */
    static public function getDataContainer()
    {
        return DataContainer::create();
    }

    /**
     * Un Successful Process Exception Handler
     *
     * @param Process $process
     * @param callable|null $function
     * @return void
     * @static
     */
    static public function unSuccessfulProcessExceptionHandler(Process $process, callable $function = null)
    {
        if (!$process->isSuccessful()) {
            if ($function) {
                $function();
            }
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
     * Creates a Build Working Directory
     *
     * @param string $prefix
     * @return string
     * @static
     */
    static public function createBuildWorkingDirectory($prefix)
    {
        $workingDirectory = CLI_HOME_BUILDS . '/' . $prefix . GeneralUtility::generateRandomString(40 - strlen($prefix));
        self::getFileSystem()->mkdir($workingDirectory);
        return $workingDirectory;
    }

}