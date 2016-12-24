<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitbucketDeleteRepoCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketDeleteRepoCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('bitbucket:deleterepo');
        $this->setDescription('Deletes a repository');
        $this->addArgument('project', InputArgument::OPTIONAL, 'The key of the project where to list the repositories from.');
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
            $project = $projects[$this->io->choice('From which project would you choose a repository to delete?', $choices)];
        }
        // Gets the repositories belonging to this project and offer a list of choices to the user
        $choices = [];
        $repositories = [];
        /* @var Repository $repository */
        foreach ($bitbucket->getRepositoriesByProject($project->getKey()) as $key => $repository) {
            $repositories[$repository->getName()] = $repository;
            $choices[] = $repository->getName();
        }
        $repository = $repositories[$this->io->choice('Select the repository you want to delete', $choices)];
        $repositoryName = $repository->getProject()->getKey() . '/' . $repository->getName();
        // This asks for some confirmation
        if ($this->io->confirm('Are you sure you want to delete the repository: "' . $repositoryName . '"?"', false)) {
            $repository->delete();
            $this->io->success('The repository "' . $repositoryName . '" is successfully deleted.');
        } else {
            $this->io->note('Command aborted. Keep calm and carry on.');
        }
    }

}