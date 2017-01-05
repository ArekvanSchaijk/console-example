<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\StringUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppAssistMeCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppAssistMeCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:assistme');
        $this->setDescription('Runs diagnostics for detecting problems inside the application');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();
        $search = 'evaluate';
        // Runs all methods from the current class ending with 'evaluate'
        foreach (get_class_methods($this) as $method) {
            if (substr($method, 0, strlen($search)) === $search) {
                $this->$method($app);
            }
        }
    }

    /**
     * Evaluates the Apache ErrorLog
     *
     * @param App $app
     * @return void
     */
    protected function evaluateRelativeWorkingDirectory(App $app)
    {
        if (!$app->isApplicationDirectory()) {
            $this->io->warning('The application has no application directory (' .
                $this->config->app()->getRelativeWorkingDirectory() . ')');
        }
    }

    /**
     * Evaluates the Configuration File
     *
     * @param App $app
     * @return void
     */
    protected function evaluateConfigurationFile(App $app)
    {
        if (!$app->hasConfigFile()) {
            $this->io->warning('The application has no "' . $this->config->app()->getRelativeConfigFilePath() . '" file.');
            // Stops further evaluations
            exit;
        }
    }

    /**
     * Evaluates the Local Working Directory
     *
     * @param App $app
     * @return void
     */
    protected function evaluateLocalWorkingDirectory(App $app)
    {
        foreach ($app->getLocalDirectoriesAndFiles() as $path => $type) {
            if (!file_exists($path)) {
                switch ($type) {
                    case 'file':
                        $this->io->warning('The file "' . $path . '" does not exists. This might be fixed with the'
                            . ' command: \'app:buildlocal\'.');
                        break;
                    default:
                        $this->io->warning('The directory "' . $path . '" does not exists. This might be fixed with'
                            . ' the command: \'app:buildlocal\'.');
                }
            }
        }
    }

    /**
     * Evaluates the Apache ErrorLog
     *
     * @param App $app
     * @return void
     */
    protected function evaluateApacheErrorLog(App $app)
    {
        if ($app->apache()->hasErrorLog()) {
            foreach ($app->apache()->getErrors(10) as $error) {
                // Shows a maximum of 10 errors from the log in the past 2 hours
                if ($error->getTimestamp() && $error->getTimestamp() > time() - 7200) {
                    $this->io->warning('An ' . $error->getSeverity() . ' from the apache error log ('
                        . StringUtility::timeElapsed('@' . $error->getTimestamp()) . ')'
                        . PHP_EOL . PHP_EOL . $error->getMessage() . PHP_EOL . PHP_EOL . 'Please check the'
                        . ' apache error log for more details.');
                }
            }
        }
    }

    /**
     * Evaluate Current Environment
     *
     * @param App $app
     * @return void
     */
    protected function evaluateCurrentEnvironment(App $app)
    {
    }

}