<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\AppUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppRemoveCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppRemoveCommand extends CommandBase
{

    /**
     * @var App
     */
    protected $app;

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:remove');
        $this->setDescription('Removes the application');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app we're working with
        $app = AppUtility::load();
        // This asks the user for confirmation
        if ($this->io->confirm('Are you sure you would like to remove the application "'
            . $app->getBasename() . '"?', false)
        ) {
            $app->remove();
            $this->io->success($app->getBasename() . ' is successfully removed.');
        } else {
            $this->io->note('Command aborted. Keep calm and carry on.');
        }
    }

}