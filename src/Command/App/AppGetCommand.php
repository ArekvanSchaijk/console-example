<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\GeneralUtility;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppGetCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppGetCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:get');
        $this->setDescription('Gets an application');
        $this->addArgument('application', InputArgument::OPTIONAL);
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
        // This prevents the command from executing within a application's directory
        $this->preventBeingWithinAnApp();
        // Gets all repositories belonging to an app
        $repositories = $this->bitbucketDriver()->getAppRepositories();
        // Creates an array with the app options
        $choices = [];
        foreach ($repositories as $key => $_) {
            $choices[] = $key;
        }
        // Selects the repository of the app we want to get
        $repository = null;
        // Checks if the $input argument 'application' was given
        if ($input->getArgument('application')) {
            if (isset($repositories[$input->getArgument('application')])) {
                $repository = $repositories[$input->getArgument('application')];
            } else {
                $this->io->error('The given application does not exists.');
            }
        }
        // If $repository is not defined here (i.e. by $input argument) then we offer a list of choices
        if (!$repository) {
            $option = $this->io->choice('Select the application you want to get', $choices);
            $repository = $repositories[$option];
        }
        // Notes that the process can take some time.
        $this->io->note('The application will now be retrieved. This can take some time.');
        // Creates a new (still empty) app
        $app = AppUtility::createNewApp(
            $repository->getSshCloneUrl()
        );
        // If the branch belonging to this environment exists than we check out to it
        if ($app->doesCurrentEnvironmentBranchExists()) {
            $app->checkoutCurrentEnvironment();
        }
        // Creates a temporary directory name
        $temporaryDirectoryName = $repository->getSlug() . '_' . GeneralUtility::generateRandomString(10);
        // If there is no application config file
        if (!$app->getMostRecentConfig()) {
            // Moves the application to a temporary directory inside cwd
            $app->move(getcwd() . '/' . $temporaryDirectoryName);
            // Shows a warning about it
            $this->io->warning('The application has (still) no configuration file ('
                . $this->config->app()->getRelativeConfigFilePath() . ').');
            // Notify's the user about the temporary created directory
            $this->io->note('The application is created in the directory named: "' . $temporaryDirectoryName . '".');
        } else {
            // Uses the server name of the application as the new directory name
            $directoryName = $app->getMostRecentConfig()->current()->getServerName();
            // If this server name is unknown (e.g. missing) then we use the temporary name
            if (!$directoryName) {
                $app->move(getcwd() . $temporaryDirectoryName);
                // And here we notify about it
                $this->io->note('Could not resolve the server name for the current environment. The application is '
                    . 'created in the directory named: "' . $temporaryDirectoryName . '".');
            } else {
                $newWorkingDirectory = getcwd() . '/' . $directoryName;
                // Actions when the directory of the new application already exists
                if (file_exists($newWorkingDirectory)) {
                    // Asks if the user wants to abort the operation
                    if ($this->io->confirm('The application directory "' . $directoryName . '" does already exists. Do'
                        . ' you want to abort the app:get operation?', false)
                    ) {
                        // Removes the app (including all build files)
                        $app->remove();
                        // And here we notify about it
                        $this->io->note('Command aborted. All build files are removed.');
                        exit;
                    }
                    // Asks the user if he would like to backup the already existing app
                    if ($this->io->confirm('Do you want to backup the already existing app?')) {
                        // Loads the existing application
                        $existingApp = AppUtility::load($newWorkingDirectory);
                        $backupWorkingDirectory = $existingApp->backup();
                    }
                }
                $app->move($newWorkingDirectory);
            }
        }
        // Builds the application
        $this->io->note('Building... This can take some time.');
        $app->build();
    }

}