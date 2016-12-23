<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
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
        $this->setDescription('Lists all projects');
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
        // Gets the bitbucket api
        $bitbucket = $this->bitbucketDriver()->getApi();
        $rows = [];
        /* @var Project $project */
        foreach ($bitbucket->getProjects() as $project) {
            if ($this->passItemsThroughFilter([$project->getKey(), $project->getName()])
            ) {
                $rows[] = [
                    $project->getId(),
                    $this->highlightFilteredWords($project->getKey()),
                    $this->highlightFilteredWords($project->getName()),
                    $project->getType(),
                    ($project->getIsPublic() ? '1' : ''),
                    $project->getLink()
                ];
            }
        }
        $count = count($rows);
        $this->renderFilter($count);
        if ($count) {
            $headers = [
                '#', 'Key', 'Name', 'Type', 'Public', 'Link'
            ];
            $this->io->table($headers, $rows);
        }
    }

}