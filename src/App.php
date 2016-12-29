<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Config as CliConfig;
use AlterNET\Cli\App\Config as AppConfig;
use AlterNET\Cli\App\Exception;
use AlterNET\Cli\App\Service\ComposerService;
use AlterNET\Cli\App\Service\GitService;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
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
    protected $gitService;

    /**
     * @var ComposerService
     */
    protected $composerService;

    /**
     * App constructor.
     * @param string $workingDirectory
     */
    public function __construct($workingDirectory)
    {
        $this->cliConfig = ConsoleUtility::getConfig();
        $this->setWorkingDirectory($workingDirectory);
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
        // Composer install
        if (file_exists($this->getWorkingDirectory() . '/composer.lock')) {
            $this->getComposerService()->install();
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
            $remoteBranches = $this->getGitService()->getRemoteBranches();
            $remoteUrl = $this->getRemoteUrl();
            foreach (AppUtility::getDefaultEnvironmentBranchNames() as $branchName) {
                if (in_array($branchName, $remoteBranches)) {
                    $tempApp = AppUtility::createNewApp($remoteUrl);
                    $tempApp->getGitService()->checkout($branchName);
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
     * Gets the Git Service
     *
     * @return GitService
     */
    public function getGitService()
    {
        if (!$this->gitService) {
            $this->gitService = new GitService($this);
        }
        return $this->gitService;
    }

    /**
     * Gets the Composer Service
     *
     * @return ComposerService
     */
    public function getComposerService()
    {
        if (!$this->composerService) {
            $this->composerService = new ComposerService($this);
        }
        return $this->composerService;
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
    public function process($commandLine, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = [])
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
        foreach ($this->getGitService()->getRemoteBranches() as $remoteBranch) {
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
        $this->getGitService()->checkout($currentEnvironmentBranch);
    }

    /**
     * Gets the Remote Url
     *
     * @return string
     */
    public function getRemoteUrl()
    {
        return $this->getGitService()->getRemoteUrl();
    }

}