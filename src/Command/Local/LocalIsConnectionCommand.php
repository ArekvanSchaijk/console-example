<?php
namespace AlterNET\Cli\Command\Local;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ConsoleUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocalIsConnectionCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalIsConnectionCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('local:isconnection');
        $this->setDescription('Tells if there is an internet connection or not');
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
        if (ConsoleUtility::isInternetConnection()) {
            $this->io->success('Successfully connected to www.google.com');
        } else {
            $this->io->error('Internet connection could not be established');
        }
    }

}