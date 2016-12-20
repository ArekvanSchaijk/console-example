<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\CurrentProjectUtility;
use AlterNET\Cli\Utility\ProjectUtility;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository\Branch;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AppGetCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppGetdsfCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:get');
        $this->setDescription('Gets an application');
        $this->addArgument('project', InputArgument::OPTIONAL);
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (CurrentProjectUtility::isCwdInProject()) {
            $this->io->error('It\'s not possible to get a project inside the directory of an existing project. '
                . 'Please browse to your web root directory and try again.');
        } else {
            // Collects the Crowd Container
            $crowd = $this->processCollectCrowdCredentials();
            // Retrieves all Repositories belonging to a project
            $repositories = ProjectUtility::getRepositories($crowd);
            $choices = [];
            foreach ($repositories as $key => $repository) {
                $choices[] = $key;
            }
            // Select project
            if ($input->getArgument('project')) {
                if (isset($repositories[$input->getArgument('project')])) {
                    $repository = $repositories[$input->getArgument('project')];
                } else {
                    $this->io->error('No project with key ' . $input->getArgument('project') . ' exists');
                    $repository = $repositories[$this->io->choice('Select the project you want to get', $choices)];
                }
            } else {
                $repository = $repositories[$this->io->choice('Select the project you want to get', $choices)];
            }
            // Select branch
            $branchName = null;
            if (($branch = ProjectUtility::getCurrentEnvironmentBranch($repository))) {
                $branchName = $branch->getName();
            } else {
                $choices = [];
                /* @var Branch $branch */
                foreach ($repository->getBranches() as $branch) {
                    if (ProjectUtility::isEnvironmentBranchName($branch->getName())) {
                        $choices[] = $branch->getName();
                    }
                }
                if ($choices) {
                    $branchName = $this->io->choice('Select the environment you want to check out', $choices);
                }
            }
            // Creates a temporary directory
            $temporaryDirectory = getcwd() . '/alternet_temp_' . md5(microtime());
            // Clones the project into the temporary directory
            ConsoleUtility::gitClone($repository->getSshCloneUrl(), $temporaryDirectory);
            if ($branchName) {
                // Check out on the given branch name
                ConsoleUtility::gitCheckout($branchName, $temporaryDirectory);
                // Gets the projects app config
                $appConfig = ProjectUtility::getConfig($temporaryDirectory);
                if (($domain = $appConfig->current()->getServerName())) {
                    $directory = getcwd() . '/' . $domain;
                    if (file_exists($directory)) {
                        if (($this->io->confirm('The directory "' . $domain . '" does already exists. Would you like to '
                            . 'remove the existing directory?'))
                        ) {
                            $fileSystem = new Filesystem();
                            $fileSystem->remove($directory);
                        } elseif ($this->io->confirm('Do you want to backup the old directory?')) {
                            rename($directory, $directory . '_backup_' . date('Y-m-d_H-i-s'));
                        } else {
                            $directory = $directory . '_' . substr(md5(microtime()), 0, 6);
                            $this->io->note('The project is written to directory ' . $directory);
                        }
                    }
                    rename($temporaryDirectory, $directory);
                    if (file_exists($directory . '/composer.lock')) {
                        ConsoleUtility::composerInstall($directory);
                    }
                }
            }
            $this->io->success('The project is successfully retrieved.');
        }
    }

}