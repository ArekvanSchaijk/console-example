<?php
namespace AlterNET\Cli\Command\Project;

use AlterNET\Cli\Command\CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProjectSyncCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ProjectSyncCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('project:sync');
        $this->setDescription('Synchronizes the user content and/or database');
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

    }

}