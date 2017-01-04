<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppLogCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppLogCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:log');
        $this->setDescription('View log files of the app');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = AppUtility::load();

        $app->parseErrorLog();
    }

}