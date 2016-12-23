<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\GeneralUtility;
use AlterNET\Cli\Utility\StringUtility;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class BitbucketCreateRepoCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketCreateRepoCommand extends CommandBase
{

    const

        VALIDATOR_DEFAULT = 'validator_default',
        VALIDATOR_EXTENSION_NAME = 'validator_extension_name',
        VALIDATOR_PACKAGE_NAME = 'validator_package_name';

    const

        EXECUTE_HIPCHAT_INTEGRATION = 'excute_hipchat_integration',
        EXECUTE_CREATE_MASTER_BRANCH = 'execute_create_master_branch';

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('bitbucket:createrepo');
        $this->setDescription('Creates a new repository');
        $this->addArgument('project', InputArgument::OPTIONAL, 'The project key where the repository should be created.');
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
        // Creates a array with project to choose from
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
            $project = $projects[$this->io->choice('In which project would you create the new repository?', $choices)];
        }
        // Gets the project's 'create_repo' options
        $options = $this->config->bitbucket()->getProjectCreateRepoOptions($project->getKey());
        // Creates a new repository
        $repository = new Repository();
        // Sets the repository name
        $repository->setName($this->io->ask('Repository name', null, function ($value) use ($options) {
            $value = trim($value);
            if (empty(str_replace(['_', '-', ' '], null, $value))) {
                throw new Exception('The name cannot be empty.');
            }
            foreach ($options as $option) {
                switch ($option) {

                    // Default validator
                    case self::VALIDATOR_DEFAULT:
                        if (preg_match('/\s/', $value)) {
                            throw new Exception('It is not allowed to use spaces.');
                        }
                        break;

                    // Extension name validator
                    case self::VALIDATOR_EXTENSION_NAME:
                        if (strpos($value, '-') !== FALSE) {
                            throw new Exception('It is not allowed to use a dash (-) in an extension key. Use'
                                . ' underscores instead.');
                        }
                        if (strpos($value, '__') !== FALSE) {
                            throw new Exception('It is not allowed to place multiple underscores (_) in a row.');
                        }
                        if (preg_match('/[^a-z_0-9]/', $value)) {
                            throw new Exception('The key of the extension cannot contain characters other then a-z,'
                                . ' 0-9 and _.');
                        }
                        if (preg_match('/^[\d_]/', $value)) {
                            throw new Exception('The key of the extension cannot start with a digit or underscore (_).');
                        }
                        if (preg_match('/[\d_]$/', $value)) {
                            throw new Exception('The key of the extension cannot ends with a digit or underscore (_).');
                        }
                        $lengthWithoutUnderscores = strlen(str_replace('_', null, $value));
                        if ($lengthWithoutUnderscores < 3 || $lengthWithoutUnderscores > 30) {
                            throw new Exception('The key of the extension must have minimum 3, maximum 30 characters'
                                . ' (not counting underscores)');
                        }
                        foreach (['tx', 'user_', 'pages', 'tt_', 'sys_', 'ts_language_', 'csh_'] as $forbiddenStart) {
                            if (substr($value, 0, strlen($forbiddenStart)) === $forbiddenStart) {
                                throw new Exception('The key of the extension cannot start with: "'
                                    . $forbiddenStart . '"');
                            }
                        }
                        break;

                    // Package name validator
                    case self::VALIDATOR_PACKAGE_NAME:
                        if (strpos($value, '_') !== FALSE) {
                            throw new Exception('It is not allowed to use a underscore (_) in a package name. Use'
                                . ' a dash (-) instead.');
                        }
                        if (strpos($value, '--') !== FALSE) {
                            throw new Exception('It is not allowed to place multiple dashes (-) in a row.');
                        }
                        if (preg_match('/[^a-zA-Z\-0-9]/', $value)) {
                            throw new Exception('The name of the package cannot contain characters other then a-z,'
                                . ' A-Z, 0-9 and -.');
                        }
                        if (StringUtility::getFirstCharacter($value) === '-'
                            || StringUtility::getLastCharacter($value) === '-'
                        ) {
                            throw new Exception('The name of the package cannot start or ends with a dash (-).');
                        }
                        break;

                    default:
                        // Do nothing here :)
                        break;
                }
            }
            return $value;
        }));
        // And finally creates the new repository through the bitbucket API
        // A friendly note: the exception handling is done by the api itself
        $repository = $project->createRepository($repository);
        $this->io->success('The repository has been created successfully.');
        // Runs the execute options
        foreach ($options as $option) {
            switch ($option) {

                case self::EXECUTE_CREATE_MASTER_BRANCH:
                    $this->executeCreateMasterBranch($repository);
                    break;

                case self::EXECUTE_HIPCHAT_INTEGRATION:
                    $this->executeHipChatIntegration($repository);
                    break;

                default:
                    // Do nothing here :)
                    break;

            }
        }
    }

    /**
     * Executes the Creation of the Master Branch
     *
     * @param Repository $repository
     * @return void
     */
    protected function executeCreateMasterBranch(Repository $repository)
    {
        // This clones the repository locally
        $buildDirectory = CLI_HOME_BUILDS . '/createrepo_' . GeneralUtility::generateRandomString(20);
        $process = new Process('git clone ' . $repository->getSshCloneUrl() . ' ' . $buildDirectory);
        $process->run();
        if (!$process->isSuccessful()) {
            if (file_exists($buildDirectory)) {
                ConsoleUtility::fileSystem()->remove($buildDirectory);
            }
            ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
        } else {
            ConsoleUtility::fileSystem()->touch($buildDirectory . '/master.txt');
            $process = new Process('git add .;git commit -m \'Initial commit of master.txt\';git push origin master');
            $process->run();
        }
    }

    /**
     * Executes the HipChat Integration
     *
     * @param Repository $repository
     * @return void
     */
    protected function executeHipChatIntegration(Repository $repository)
    {
        if (($roomId = $this->config->bitbucket()->getProjectHipChatRoomId($repository->getProject()->getKey()))) {
            try {
                $repository->createHipChatIntegration($roomId);
                $this->io->success('The repository has been integrated with HipChat successfully.');
            } catch (\Exception $exception) {
                $this->io->error('Could not create the HipChat integration.');
            }
        }
    }

}