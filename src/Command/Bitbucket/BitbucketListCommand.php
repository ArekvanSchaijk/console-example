<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ServiceUtility;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitbucketListProjects
 * @author Arek van Schaijk <info@ucreation.nl>
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
        $bitbucket = ServiceUtility::getBitbucketService();
        $table = new Table($output);
        $table->setHeaders(
            ['ID', 'Key', 'Name', 'Type', 'Public', 'Link']
        );
        $rows = [];
        /* @var Project $project */
        foreach ($bitbucket->getApi()->getProjects() as $project) {
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