<?php
namespace AlterNET\Cli\Command\Project;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ProjectUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ProjectListCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ProjectListCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('project:list');
        $this->setDescription('Lists all project');
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
        $io = new SymfonyStyle($input, $output);
        // Collects the Crowd Container
        $crowd = $this->processCollectCrowdCredentials($io);
        // Retrieves all Repositories belonging to a project
        $repositories = ProjectUtility::getRepositories($crowd);


        $choices = [];
        foreach ($repositories as $repository) {
            $choices[] = $repository->getProject()->getKey() . '/' . $repository->getName();
        }

        $io->choice('Select the project you wish to get', $choices);

    }

}