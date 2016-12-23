<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\IOHelperUtility;
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
        $rows = [];
        /* @var Project $project */
        foreach ($this->bitbucketDriver()->getApi()->getProjects() as $project) {
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
            $this->io->table(IOHelperUtility::getBitbucketProjectHeaders(), $rows);
        }
    }

}