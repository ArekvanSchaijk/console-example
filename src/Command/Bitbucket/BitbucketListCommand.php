<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use ArekvanSchaijk\BitbucketServerClient\Api;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitbucketListCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketListCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('bitbucket:list');
        $this->setDescription('Lists all projects from Bitbucket');
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
        // Retrieves the Crowd Credentials
        $credentials = $this->processCollectCrowdCredentials($input, $output);
        // Creates a new Bitbucket API
        $bitbucket = new Api();
        // Sets the Bitbucket endpoint from the Cli Config
        $bitbucket->setEndpoint(
            self::$config->getBitbucketEndpoint()
        );
        // Logs into Bitbcuket with the given Crowd Credentials
        $bitbucket->login(
            $credentials->username,
            $credentials->password
        );




        $table = new Table($output);
        $table->setHeaders(
            ['ID', 'Key', 'Name', 'Type', 'Public', 'Link']
        );
        $rows = [];
        /* @var Project $project */
        foreach ($bitbucket->getProjects() as $project) {
            $rows[] = [
                $project->getId(),
                $project->getKey(),
                $project->getName(),
                $project->getType(),
                ($project->getIsPublic() ? '1' : ''),
                $project->getLink()
            ];
        }
        $table->setRows($rows);
        $table->render();
    }

}