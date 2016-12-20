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
        // Creates a new (still empty) app
        $app = AppUtility::createNewApp(
            $repository->getSshCloneUrl()
        );
        // If the branch belonging to this environment exists than we check out to it
        if ($app->doesCurrentEnvironmentBranchExists()) {
            $app->checkoutCurrentEnvironment();
        }
        // If there is no application config file
        if (!$app->hasConfigFile()) {
            // Moves the application to a temporary directory inside cwd
            $newDirectoryName = $repository->getSlug() . '_' . GeneralUtility::generateRandomString(10);
            $newWorkingDirectory = getcwd() . '/' . $newDirectoryName;
            $app->move($newWorkingDirectory);
            // Shows a warning about it
            $this->io->warning('The application has (still) no configuration file ('
                . $this->config->app()->getRelativeConfigFilePath() . ').');
            // Notify's the user about the temporary created directory
            $this->io->note('The application is created in the directory named "' . $newDirectoryName . '".');
        } else {

            echo $app->getConfig()->current()->getServerName();
        }
    }

}