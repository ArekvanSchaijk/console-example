<?php
namespace AlterNET\Cli\Command\Project;

use AlterNET\Cli\Command\CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProjectEvaluateCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ProjectEvaluateCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('project:evaluate');
        $this->setDescription('Evaluates the project');
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