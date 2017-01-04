<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppClearLogsCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppClearLogsCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:clearlogs');
        $this->setDescription('Clears all log files of the application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();

        if ($app->isApplicationDirectory()) {
            $app->createDirectoriesAndFiles();
            foreach ($app->getLogs() as $filePath) {
                file_put_contents($filePath, '');
            }
            $this->io->success('The logs from the application are successfully cleared.');
        } else {
            $this->io->error('There is no application directory (' .
                $this->config->app()->getRelativeWorkingDirectory() . ').');
        }
    }

}