<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppBackupCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppBackupCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:backup');
        $this->setDescription('Creates a copy of the application in the backup directory');
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
        // This asks the user for confirmation
        if ($this->io->confirm('Are you sure you would like to create a copy of the application "'
            . $app->getBasename() . '"?')
        ) {
            $backupPath = $app->backup();
            $this->io->success($app->getBasename() . ' is successfully copied to "' . $backupPath . '".');
        } else {
            $this->io->note('Command aborted.');
        }
    }

}