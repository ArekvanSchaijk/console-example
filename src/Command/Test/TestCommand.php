<?php
namespace AlterNET\Cli\Command\Test;

use AlterNET\Cli\Command\CommandBase;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class TestCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('test:test');
        $this->setDescription('Just a rest command');
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
        // Gets the bitbucket api
        $bitbucket = $this->bitbucketDriver()->getApi();
        // Asks the user for the project name
        $projectName = trim($this->io->ask('Project name'));
        // Asks the user for the project key
        $projectKey = trim(strtoupper($this->io->ask('Project key (uppercase)')));
        // And creates a new project
        $newProject = new Project();
        $newProject->setName($projectName);
        $newProject->setKey($projectKey);
        $project = $bitbucket->createProject($newProject);


        $this->io->table('',               [
            $project->getId(),
            $this->highlightFilteredWords($input, $project->getKey()),
            $this->highlightFilteredWords($input, $project->getName()),
            $project->getType(),
            ($project->getIsPublic() ? '1' : ''),
            $project->getLink()
        ]);
    }

}