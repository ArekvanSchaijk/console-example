<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Config as CliConfig;
use AlterNET\Cli\App\Config as AppConfig;
use AlterNET\Cli\App\Exception;
use AlterNET\Cli\App\Service\ComposerService;
use AlterNET\Cli\App\Service\GitService;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
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
     *
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
     * Does Current Environment Branch Exists
     *
     * @return bool
     */
    public function doesCurrentEnvironmentBranchExists()
    {
        return in_array(AppUtility::getCurrentEnvironmentBranchName(), $this->getEnvironmentBranches());
    }

    /**
     * Checkout Current Environment
     *
     * @return void
     * @throws Exception
     */
    public function checkoutCurrentEnvironment()
    {
        $currentEnvironmentBranch = AppUtility::getCurrentEnvironmentBranchName();
        if (!$this->doesCurrentEnvironmentBranchExists()) {
            throw new Exception('Could not check out current environment since the branch '
                . $currentEnvironmentBranch . ' does not exists on the server.');
        }
        $this->getGitService()->checkout($currentEnvironmentBranch);
    }

}