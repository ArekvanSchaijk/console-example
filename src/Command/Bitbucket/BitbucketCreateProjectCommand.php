<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\IOHelperUtility;
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
    public function configure()
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
        $project->setName($this->io->ask('Project name'));
        // Sets the project key
        $project->setKey($this->io->ask('Project key', null, function ($value) {
            $value = trim($value);
            if (!ctype_alnum($value)) {
                throw new Exception('Only letters and numbers are allowed.');
            }
            if (!ctype_upper($value)) {
                throw new Exception('The input must be upper case.');
            }
            return $value;
        }));
        // Creates the new project through the bitbucket api
        // A friendly note: the exception handling is done by the api itself
        $project = $bitbucket->createProject($project);
        // Success message and table with project displayed
        $this->io->success('The project has been created successfully.');
        $this->io->table(IOHelperUtility::getBitbucketProjectHeaders(), [[
            $project->getId(),
            $project->getKey(),
            $project->getName(),
            $project->getType(),
            ($project->getIsPublic() ? '1' : ''),
            $project->getLink()
        ]]);
    }

}