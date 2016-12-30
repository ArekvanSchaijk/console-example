<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitbucketCreateProjectCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketCreateProjectCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bitbucket:createproject');
        $this->setDescription('Creates a new project');
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
        // Gets the bitbucket api
        $bitbucket = $this->bitbucketDriver()->getApi();
        // Creates a new project object
        $project = new Project();
        // Sets the project name
        $project->setName($this->io->ask('Set the name'));
        // Sets the project key
        $project->setKey($this->io->ask('Set the key', null, function ($value) {
            $value = trim($value);
            if (empty($value)) {
                throw new Exception('The name cannot be empty.');
            }
            if (!ctype_alnum($value)) {
                throw new Exception('Only letters and numbers are allowed.');
            }
            if (!ctype_upper($value)) {
                throw new Exception('The input must be uppercase.');
            }
            return $value;
        }));
        // Creates the new project through the bitbucket api
        // A friendly note: the exception handling is done by the api itself
        $project = $bitbucket->createProject($project);
        // Success message and table with project displayed
        $this->io->success('The project has been created successfully.');
        // Runs the bitbucket:list command with a --filter which displays the created project
        $this->runCommand('bitbucket:list', [
            '--filter' => $project->getKey(),
            '--filter-no-count'
        ]);
    }

}