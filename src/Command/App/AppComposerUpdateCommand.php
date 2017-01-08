<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\App\Exception;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class AppComposerUpdateCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppComposerUpdateCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:composerupdate');
        $this->setDescription('Updates Composer through a custom branch');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();
        // Gets the repository from bitbucket
        $repository = $this->bitbucketDriver()->getRepositoryByRemoteUrl(
            $app->getRemoteUrl()
        );
        if (!$repository) {
            $this->io->error('Could not find the application in Bitbucket.');
            exit;
        }
        // Gets the Development branch
        $developmentBranchName = $this->config->bitbucket()->getDefaultDevelopmentBranch();
        $branch = $repository->getBranchByName(
            $developmentBranchName
        );
        if (!$branch) {
            $this->io->error('There is no "' . $developmentBranchName . '". This command can only work '
                . 'if this branch exists.');
            exit;
        }
        // Notes that the process can take some time.
        $this->io->note('The Composer Update will be performed now. This can take some (long) time.');
        // Creates a temporary application (leaves it in the CLI_HOME_BUILD directory)
        $tempApp = AppUtility::createNewApp($repository->getSshCloneUrl());
        // If the working branch name does not exists on the server we're creating it here
        $workingBranchName = 'CLI/ComposerUpdate';
        $workingBranch = $repository->getBranchByName($workingBranchName);
        if (!$workingBranch) {
            $workingBranch = $repository->createBranch($branch, $workingBranchName);
        }
        $tempApp->git()->checkout($workingBranch->getName());
        // Copy's the composer.json file from the $app
        if (!file_exists($app->getWorkingDirectory() . '/composer.json')) {
            $tempApp->remove();
            throw new Exception('The application has no Composer.json file.');
        }
        ConsoleUtility::getFileSystem()->remove($tempApp->getWorkingDirectory() . '/composer.json');
        ConsoleUtility::getFileSystem()->copy(
            $app->getWorkingDirectory() . '/composer.json',
            $tempApp->getWorkingDirectory() . '/composer.json'
        );
        // Save some time later by copying the vendor dir to the build directory
        if (file_exists($app->getWorkingDirectory() . '/vendor')) {
            $process = new Process('cp -a vendor ' . $tempApp->getWorkingDirectory(), $app->getWorkingDirectory());
            $process->run();
            ConsoleUtility::unSuccessfulProcessExceptionHandler($process, function () use ($tempApp) {
                $tempApp->remove();
            });
        }
        // Performs a composer install
        $tempApp->composer()->update();
    }

}