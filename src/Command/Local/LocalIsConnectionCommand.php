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
    public function configure()
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
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (ConsoleUtility::isInternetConnection()) {
            $output->writeln('<info>Yes</info>');
        } else {
            $output->writeln('<error>No</error>');
        }
    }

}