<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitbucketCreateRepoCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketListReposCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('bitbucket:listrepos');
        $this->setDescription('Lists all repositories from a project');
        $this->addArgument('project', InputArgument::OPTIONAL, 'The key of the project where to list the repositories from.');
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
        // Creates a array with projects to choose from
        $choices = [];
        $projects = [];
        /* @var Project $project */
        foreach ($bitbucket->getProjects() as $project) {
            $choices[$project->getKey()] = $project->getName();
            $projects[$project->getKey()] = $project;
        }
        // This checks if the given input argument contains the project key and checks if it exists
        $project = null;
        if ($input->getArgument('project')) {
            $projectKey = strtoupper(trim($input->getArgument('project')));
            if (!isset($choices[$projectKey])) {
                $this->io->error('There is no project with the key "' . strtoupper($projectKey) . '".');
            } else {
                $project = $projects[$projectKey];
            }
        }
        // If there was no project selected we offer here a list with project to choice from
        if (!$project) {
            $project = $projects[$this->io->choice('From which project would you list all repositories?', $choices)];
        }
        $rows = [];
        /* @var Repository $repository */
        foreach ($project->getRepositories() as $repository) {
            if ($this->passItemsThroughFilter([$repository->getName()])) {
                $rows[] = [
                    $repository->getId(),
                    $this->highlightFilteredWords($repository->getName()),
                    ($repository->getIsPublic() ? '1' : ''),
                    $repository->getSshCloneUrl(),
                ];
            }
        }
        $count = count($rows);
        $this->renderFilter($count);
        if ($count) {
            $headers = [
                '#', 'Name', 'Public', 'Clone Url'
            ];
            $this->io->table($headers, $rows);
        } else {
            $this->io->error('There are no repositories to show.');
        }
    }

}