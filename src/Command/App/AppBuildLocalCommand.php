<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppBuildLocalCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppBuildLocalCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:buildlocal');
        $this->setDescription('Builds the .alternet/local directory');
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
        if ($app->isApplicationDirectory()) {
            $app->buildLocal();
            $this->io->success('The local directory is successfully build.');
        } else {
            $this->io->warning('This application has no environments set.');
        }
    }

}