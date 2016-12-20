<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use ArekvanSchaijk\BitbucketServerClient\Api;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
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
        $this->setDescription('Lists all projects on Bitbucket');
        $this->addFilterOption();
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
        $credentials = $this->processCollectCrowdCredentials();
        // Creates a new Bitbucket API
        $bitbucket = new Api();
        // Sets the Bitbucket endpoint from the Cli Config
        $bitbucket->setEndpoint($this->config->bitbucket()->getEndpoint());
        // Logs into Bitbcuket with the given Crowd Credentials
        $bitbucket->login($credentials->username, $credentials->password);
        $rows = [];
        /* @var Project $project */
        foreach ($bitbucket->getProjects() as $project) {
            if ($this->passItemsThroughFilter($input, [
                $project->getKey(),
                $project->getName()
            ])
            ) {
                $rows[] = [
                    $project->getId(),
                    $this->highlightFilteredWords($input, $project->getKey()),
                    $this->highlightFilteredWords($input, $project->getName()),
                    $project->getType(),
                    ($project->getIsPublic() ? '1' : ''),
                    $project->getLink()
                ];
            }
        }
        $count = count($rows);
        $this->renderFilter($input, $output, $count);
        if ($count) {
            $headers = [
                '#', 'Key', 'Name', 'Type', 'Public', 'Link'
            ];
            $this->io->table($headers, $rows);
        }
    }

}