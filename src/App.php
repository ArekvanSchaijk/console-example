<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Config as CliConfig;
use AlterNET\Cli\App\Config as AppConfig;
use AlterNET\Cli\App\Exception;
use AlterNET\Cli\App\Service\ComposerService;
use AlterNET\Cli\App\Service\GitService;
use AlterNET\Cli\Utility\ApacheUtility;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\StringUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Process\Process;

/**
 * Class App
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class App
{

    /**
     * @var AppConfig
     */
    protected $config;

    /**
     * @var AppConfig|bool
     */
    protected $mostRecentConfig;

    /**
     * @var CliConfig
     */
    protected $cliConfig;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var string
     */
    protected $previousWorkingDirectory;

    /**
     * @var GitService
     */
    protected $git;

    /**
     * @var ComposerService
     */
    protected $composer;

    /**
     * App constructor.
     * @param string $workingDirectory
     */
    public function __construct($workingDirectory)
    {
        $this->cliConfig = ConsoleUtility::getConfig();
        $this->setWorkingDirectory($workingDirectory);
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * Gets the Working Directory
     *
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * Gets the Web Working Directory
     *
     * @return string
     */
    public function getWebWorkingDirectory()
    {
        if ($this->hasConfigFile()) {
            if (($webDirectory = $this->getConfig()->getWebDirectory())) {
                return $this->getWorkingDirectory() . $webDirectory;
            }
        }
        return $this->getWorkingDirectory();
    }

    /**
     * Gets the Local Working Directory
     *
     * @return string
     */
    public function getLocalWorkingDirectory()
    {
        return $this->getWorkingDirectory() . '/' . $this->cliConfig->app()->getRelativeLocalWorkingDirectory();
    }

    /**
     * Gets the Local Logs Working Directory
     *
     * @return string
     */
    public function getLocalLogsWorkingDirectory()
    {
        return $this->getLocalWorkingDirectory() . '/logs';
    }

    /**
     * Gets the Virtual Host File Path
     *
     * @return string
     */
    public function getVirtualHostFilePath()
    {
        return $this->getLocalWorkingDirectory() . '/vhost.conf';
    }

    /**
     * Gets the Error Log File Path
     *
     * @return string
     */
    public function getErrorLogFilePath()
    {
        return $this->getLocalLogsWorkingDirectory() . '/error.log';
    }

    /**
     * Gets the Access Log File Path
     *
     * @return string
     */
    public function getAccessLogFilePath()
    {
        return $this->getLocalLogsWorkingDirectory() . '/access.log';
    }

    /**
     * Gets the Absolute File Path
     *
     * @param string $filePath
     * @return string
     */
    public function getAbsoluteFilePath($filePath)
    {
        if (StringUtility::getFirstCharacter($filePath) !== '/') {
            return $this->getWorkingDirectory() . '/' . $filePath;
        }
        return $filePath;
    }

    /**
     * Gets the Basename
     *
     * @return string
     */
    public function getBasename()
    {
        return basename($this->getWorkingDirectory());
    }

    /**
     * Gets the Previous Basename
     *
     * @return string
     */
    public function getPreviousBasename()
    {
        return basename($this->getPreviousWorkingDirectory());
    }

    /**
     * Sets the Previous Working Directory
     *
     * @param string $previousWorkingDirectory
     * @return void
     */
    public function setPreviousWorkingDirectory($previousWorkingDirectory)
    {
        $this->previousWorkingDirectory = rtrim($previousWorkingDirectory, '/');
    }

    /**
     * Gets the Previous Working Directory
     *
     * @return string
     */
    public function getPreviousWorkingDirectory()
    {
        return $this->previousWorkingDirectory;
    }

    /**
     * Sets the Working Directory
     *
     * @param string $workingDirectory
     * @throws Exception
     */
    public function setWorkingDirectory($workingDirectory)
    {
        $workingDirectory = rtrim($workingDirectory, '/');
        if (!file_exists($workingDirectory)) {
            throw new Exception('Could not set the working directory since the path "'
                . $workingDirectory . '" to it does not exists.');
        }
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * Touch
     *
     * @param string $file
     * @return string
     */
    public function touch($file)
    {
        ConsoleUtility::fileSystem()->touch($this->getAbsoluteFilePath($file));
        return $file;
    }

    /**
     * File Put Contents
     *
     * @param string $file
     * @param string $content
     * @return int
     */
    public function filePutContents($file, $content)
    {
        return file_put_contents($this->getAbsoluteFilePath($file), $content);
    }

    /**
     * Move
     * Moves the project to a new working directory
     *
     * @param string $newWorkingDirectory
     * @return void
     */
    public function move($newWorkingDirectory)
    {
        $this->setPreviousWorkingDirectory(
            $this->getWorkingDirectory()
        );
        rename($this->getPreviousWorkingDirectory(), $newWorkingDirectory);
        $this->setWorkingDirectory($newWorkingDirectory);
    }

    /**
     * Copy's the project to a working directory
     *
     * @param string $toWorkingDirectory
     * @return void
     */
    public function copy($toWorkingDirectory)
    {
        $this->process('cp -r . ' . $toWorkingDirectory);
    }

    /**
     * Remove
     * Removes the application
     *
     * @return void
     */
    public function remove()
    {
        ConsoleUtility::fileSystem()->remove($this->getWorkingDirectory());
    }

    /**
     * Creates a Backup Directory
     *
     * @return string
     */
    public function createBackupDirectory()
    {
        $date = date('Y-m-d_H-i-s');
        // Gets the root backup directory
        if (!($rootBackupDirectory = $this->cliConfig->local()->getBackupPath())) {
            $rootBackupDirectory = CLI_DEFAULT_BACKUP_PATH;
        }
        // Creates a new backup directory
        $backupDirectory = $rootBackupDirectory . '/' . $date . '_' . $this->getBasename();
        if ($this->hasConfigFile() && $this->getConfig()->getApplicationKey()) {
            $backupDirectory = $rootBackupDirectory . '/' . $this->getConfig()->getApplicationKey() . '/' . $date;
        }
        ConsoleUtility::fileSystem()->mkdir($backupDirectory);
        return $backupDirectory;
    }

    /**
     * Backup
     *
     * @return string
     */
    public function backup()
    {
        $backupDirectory = $this->createBackupDirectory();
        $this->copy($backupDirectory);
        return $backupDirectory;
    }

    /**
     * Build
     * Builds the application
     *
     * @return void
     */
    public function build()
    {
        // Builds all directories and files
        $this->buildDirectoriesAndFiles();
        // Builds the virtual host file
        $this->buildVirtualHostFile();
        // Composer install
        if (file_exists($this->getWorkingDirectory() . '/composer.lock')) {
            $this->composer()->install();
        }
        if ($this->hasConfigFile()) {
            // Performs the environment builds
            if (($builds = $this->getConfig()->current()->getBuilds())) {
                $this->processBuildCommands($builds);
            }
            // Performs the application builds
            if (($builds = $this->getConfig()->getBuilds())) {
                $this->processBuildCommands($builds);
            }
        }
    }

    /**
     * Builds all Directories And Files
     *
     * @return void
     */
    protected function buildDirectoriesAndFiles()
    {
        // This creates the /local section of the .alternet directory (if present)
        if (file_exists($this->getWorkingDirectory() . '/.alternet')) {
            // .alternet/local
            if (!file_exists($this->getLocalWorkingDirectory())) {
                ConsoleUtility::fileSystem()->mkdir($this->getLocalWorkingDirectory());
            }
            // .alternet/local/logs
            if (!file_exists($this->getLocalLogsWorkingDirectory())) {
                ConsoleUtility::fileSystem()->mkdir($this->getLocalLogsWorkingDirectory());
            }
            // .alternet/local/vhost.conf
            if (!file_exists($this->getVirtualHostFilePath())) {
                ConsoleUtility::fileSystem()->touch($this->getVirtualHostFilePath());
            }
            // .alternet/local/logs/error.log
            if (!file_exists($this->getErrorLogFilePath())) {
                ConsoleUtility::fileSystem()->touch($this->getErrorLogFilePath());
            }
            // .alternet/local/access.log
            if (!file_exists($this->getAccessLogFilePath())) {
                ConsoleUtility::fileSystem()->touch($this->getAccessLogFilePath());
            }
        }
    }

    /**
     * Process Build Commands
     *
     * @param array $commands
     * @return void
     */
    protected function processBuildCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->process($command);
        }
    }

    /**
     * Builds the application Database
     *
     */
    public function buildDatabase()
    {
    }

    /**
     * Builds the Virtual Host File
     *
     * @return void
     */
    public function buildVirtualHostFile()
    {
        if ($this->hasConfigFile()) {
            ApacheUtility::generateVirtualHostString(
                80,
                $this->getWebWorkingDirectory(),
                $this->getConfig()->current()->getDomains(),
                $this->getErrorLogFilePath(),
                $this->getAccessLogFilePath(),
                $this->cliConfig->getApplicationServerAdmin()
            );
        }


        if ($this->hasConfigFile() && $this->getConfig()->current()->getDomains()) {
            // This makes sure the web directory exists before setting the DocumentRoot path with realpath()
            if (!file_exists($this->getWebWorkingDirectory())) {
                ConsoleUtility::fileSystem()->mkdir($this->getWebWorkingDirectory());
            }
            $string = '<VirtualHost *:80>' . PHP_EOL;
            $string .= chr(9) . 'DocumentRoot' . chr(9) . '"' . realpath($this->getWebWorkingDirectory()) . '"' . PHP_EOL;
            $i = 0;
            if (($domains = $this->getConfig()->current()->getDomains())) {
                foreach ($domains as $domain) {
                    switch ($i) {
                        case 0:
                            $string .= chr(9) . 'ServerName' . chr(9) . $domain . PHP_EOL;
                            break;
                        default:
                            $string .= chr(9) . 'ServerAlias' . chr(9) . $domain . PHP_EOL;
                    }
                    $i++;
                }
            }
            $string .= chr(9) . 'ErrorLog' . chr(9) . '"' . realpath($this->getErrorLogFilePath()) . '"' . PHP_EOL;
            $string .= chr(9) . 'CustomLog' . chr(9) . '"' . realpath($this->getAccessLogFilePath()) . '" common' . PHP_EOL;
            $string .= '</VirtualHost>';
            file_put_contents($this->getVirtualHostFilePath(), $string);
        }
    }

    /**
     * Adds the Domains To the Host File
     *
     * @param string $ip
     */
    public function addDomainsToHostFile($ip = '127.0.0.1')
    {
        $hostFile = ConsoleUtility::getHostFileService();
        if ($this->hasConfigFile()) {
            if (($domains = $this->getConfig()->current()->getDomains())) {
                foreach ($domains as $domain) {
                    $hostFile->addDomain($domain, $ip);
                }
                $hostFile->write();
            }
        }
    }

    /**
     * Gets the Config
     *
     * @return AppConfig
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->initializeConfig();
        }
        return $this->config;
    }

    /**
     * Initializes the Config
     *
     * @throws Exception
     */
    public function initializeConfig()
    {
        if (!$this->hasConfigFile()) {
            throw new Exception('Could not initialize the config for this app since the file '
                . $this->cliConfig->app()->getRelativeConfigFilePath() . ' does not exists.');
        }
        $this->config = new AppConfig($this->getConfigFilePath());
    }

    /**
     * Gets the Most Recent Config
     *
     * @return AppConfig|bool
     */
    public function getMostRecentConfig()
    {
        if (is_null($this->mostRecentConfig)) {
            $this->mostRecentConfig = false;
            $remoteBranches = $this->git()->getRemoteBranches();
            $remoteUrl = $this->getRemoteUrl();
            foreach (AppUtility::getDefaultEnvironmentBranchNames() as $branchName) {
                if (in_array($branchName, $remoteBranches)) {
                    $tempApp = AppUtility::createNewApp($remoteUrl);
                    $tempApp->git()->checkout($branchName);
                    if ($tempApp->hasConfigFile()) {
                        $this->mostRecentConfig = $tempApp->getConfig();
                        $tempApp->remove();
                        break;
                    }
                    $tempApp->remove();
                }
            }
        }
        return $this->mostRecentConfig;
    }

    /**
     * Gets the Config File Path
     *
     * @return string
     */
    public function getConfigFilePath()
    {
        return $this->getWorkingDirectory() . '/' . $this->cliConfig->app()->getRelativeConfigFilePath();
    }

    /**
     * Has Config File
     *
     * @return bool
     */
    public function hasConfigFile()
    {
        return file_exists($this->getConfigFilePath());
    }

    /**
     * Git
     *
     * @return GitService
     */
    public function git()
    {
        if (!$this->git) {
            $this->git = new GitService($this);
        }
        return $this->git;
    }

    /**
     * Gets the Composer Service
     *
     * @return ComposerService
     */
    public function composer()
    {
        if (!$this->composer) {
            $this->composer = new ComposerService($this);
        }
        return $this->composer;
    }

    /**
     * Executes a Process
     *
     * @param string $commandLine The command line to run
     * @param string|null $cwd The working directory or null to use the working dir of the current PHP process
     * @param array|null $env The environment variables or null to use the same environment as the current PHP process
     * @param mixed|null $input The input as stream resource, scalar or \Traversable, or null for no input
     * @param int|float|null $timeout The timeout in seconds or null to disable
     * @param array $options An array of options for proc_open
     * @return string
     */
    public function process($commandLine, $cwd = null, array $env = null, $input = null, $timeout = 120, array $options = [])
    {
        $cwd = (is_null($cwd) ? $this->getWorkingDirectory() : $cwd);
        $process = new Process($commandLine, $cwd, $env, $input, $timeout, $options);
        $process->run();
        ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
        return $process->getOutput();
    }

    /**
     * Gets the Environment Branches
     *
     * @return array
     */
    public function getEnvironmentBranches()
    {
        $environmentBranches = [];
        $defaultEnvironmentBranches = AppUtility::getDefaultEnvironmentBranchNames();
        foreach ($this->git()->getRemoteBranches() as $remoteBranch) {
            if (in_array($remoteBranch, $defaultEnvironmentBranches)) {
                $environmentBranches[] = $remoteBranch;
            }
        }
        return $environmentBranches;
    }

    /**
     * Gets the Current Environment Branch
     *
     * @return string|bool
     */
    public function getCurrentEnvironmentBranch()
    {
        if ($this->getMostRecentConfig() instanceof AppConfig) {
            if (($branchName = $this->getMostRecentConfig()->current()->getGitBranch())) {
                return $branchName;
            }
            if (Environment::isLocalEnvironment()) {
                if (($branchName = $this->getMostRecentConfig()->environment()->development()->getGitBranch())) {
                    return $branchName;
                }
            }
        }
        return AppUtility::getCurrentDefaultEnvironmentBranchName();
    }

    /**
     * Does Current Environment Branch Exists
     *
     * @return bool
     */
    public function doesCurrentEnvironmentBranchExists()
    {
        return in_array($this->getCurrentEnvironmentBranch(), $this->getEnvironmentBranches());
    }

    /**
     * Checkout Current Environment
     *
     * @return void
     * @throws Exception
     */
    public function checkoutCurrentEnvironment()
    {
        $currentEnvironmentBranch = $this->getCurrentEnvironmentBranch();
        if (!$this->doesCurrentEnvironmentBranchExists()) {
            throw new Exception('Could not check out current environment since the branch '
                . $currentEnvironmentBranch . ' does not exists on the server.');
        }
        $this->git()->checkout($currentEnvironmentBranch);
    }

    /**
     * Gets the Remote Url
     *
     * @return string
     */
    public function getRemoteUrl()
    {
        return $this->git()->getRemoteUrl();
    }

    /**
     * Parses the Error Log
     *
     * @return array|bool
     */
    public function parseErrorLog()
    {
        if (file_exists($this->getErrorLogFilePath())) {
            $rows = [];
            $contents = file_get_contents($this->getErrorLogFilePath());
            if (!empty(trim($contents))) {
                $regex = '/^\[([^\]]+)\] \[([^\]]+)\] (?:\[client ([^\]]+)\])?\s*(.*)$/i';
                preg_match($regex, $contents, $matches);
                print_r($matches);
            }
        }
        return false;
    }

    public function clearErrorLog()
    {
        // TODO
    }

}