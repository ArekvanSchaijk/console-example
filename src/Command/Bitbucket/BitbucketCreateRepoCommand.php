<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\ConsoleUtility;
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

        EXECUTE_CREATE_MASTER_BRANCH = 'execute_create_master_branch',
        EXECUTE_HIPCHAT_INTEGRATION = 'excute_hipchat_integration';

    const

        WIZARD_CREATE_COMPOSER_FILE = 'wizard_create_composer_file';

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bitbucket:createrepo');
        $this->setDescription('Creates a new repository');
        $this->addArgument('project', InputArgument::OPTIONAL, 'The key of the project where the repository should be created.');
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
            $project = $projects[$this->io->choice('In which project would you create the new repository?', $choices)];
        }
        // Gets the project's 'create_repo' options
        $options = $this->config->bitbucket()->getProjectCreateRepoOptions($project->getKey());
        // Creates a new repository
        $repository = new Repository();
        // Sets the repository name
        $repository->setName($this->io->ask('Set the repository name', null, function ($value) use ($options) {
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
                        if (strpos($value, '-') !== false) {
                            throw new Exception('It is not allowed to use a dash (-) in an extension key. Use'
                                . ' underscores instead.');
                        }
                        if (strpos($value, '__') !== false) {
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
                        if (strpos($value, '_') !== false) {
                            throw new Exception('It is not allowed to use a underscore (_) in a package name. Use'
                                . ' a dash (-) instead.');
                        }
                        if (strpos($value, '--') !== false) {
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
        // Creates a working directory
        $this->workingDirectory = ConsoleUtility::createBuildWorkingDirectory('createrepo_');
        // Runs the "execute" options for this repository
        foreach ($options as $option) {
            switch ($option) {

                case self::EXECUTE_HIPCHAT_INTEGRATION:
                    $this->executeHipChatIntegration($repository);
                    break;

                case self::EXECUTE_CREATE_MASTER_BRANCH:
                    $this->executeCreateMasterBranch($repository);
                    break;

                default:
                    // Do nothing here :)
                    break;

            }
        }
        // Runs the "wizards" for this repository
        foreach ($options as $option) {
            switch ($option) {
                case self::WIZARD_CREATE_COMPOSER_FILE:
                    $this->wizardCreateComposerFile($repository);
                    break;

                default:
                    // Do nothing here :)
                    break;
            }
        }
        // Removes the working directory
        ConsoleUtility::fileSystem()->remove($this->workingDirectory);
        // Runs the bitbucket:listrepos command with a --filter which displays the created repository
        $this->runCommand('bitbucket:listrepos', [
            'project' => $repository->getProject()->getKey(),
            '--filter' => $repository->getName(),
            '--filter-no-count'
        ]);
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

    /**
     * Executes the Creation of the Master Branch
     *
     * @param Repository $repository
     * @return void
     */
    protected function executeCreateMasterBranch(Repository $repository)
    {
        // This clones the repository locally
        $process = new Process('git clone ' . $repository->getSshCloneUrl() . ' .', $this->workingDirectory);
        $process->run();
        if (!$process->isSuccessful()) {
            ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
        } else {
            $masterFile = $this->workingDirectory . '/master.txt';
            // This creates the master branch
            ConsoleUtility::fileSystem()->touch($masterFile);
            file_put_contents($masterFile, date('Y-m-d H:i:s'));
            $process = new Process('git add .;git commit -m \'[CLI] Initial commit of master.txt\';git push origin master',
                $this->workingDirectory);
            $process->run();
            if ($process->isSuccessful()) {
                $this->io->success('The "master" branch has been created successfully.');
            } else {
                ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
            }
        }
    }

    /**
     * Wizard Create Composer File
     *
     * @param Repository $repository
     * @return void
     */
    protected function wizardCreateComposerFile(Repository $repository)
    {
        if ($this->io->confirm('Do you want to create a Composer.json file right away?')) {
            $composer = [];
            // Gets the Composer configuration belonging to this repository
            $config = $this->config->bitbucket()->getProjectComposerConfig($repository->getProject()->getKey());
            // Sets the vendor/name
            $vendor = $this->io->ask('Set the vendor name',
                (isset($config['default_vendor']) ? $config['default_vendor'] : null),
                function ($value) {
                    trim($value);
                    if (empty(str_replace([' ', '_', '-', '.'], null, $value))) {
                        throw new Exception('The vendor name cannot be empty.');
                    }
                    return $value;
                }
            );
            $composer['name'] = $vendor . '/' . $repository->getName();
            // Sets the description
            $composer['description'] = (string)$this->io->ask('Set the description', null, function ($value) {
                return trim($value);
            });
            // Sets the license
            $composer['license'] = $this->io->choice('Set the license', $this->config->composer()->getAvailableLicenses(),
                (isset($config['license']) ? $config['license'] : null));
            // Sets the author
            $composer['authors'] = [];
            $composer['authors'][] = $config['author'];
            // Creates the composer.json file and puts the contents into it
            $composerFilePath = $this->workingDirectory . '/composer.json';
            ConsoleUtility::fileSystem()->touch($composerFilePath);
            file_put_contents($composerFilePath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            // Notes that the process can take some time.
            $this->io->note('The file will now be generated, pushed and merged. This can take some time.');
            // Create a new branch on the server
            $masterBranch = $repository->getBranchByName('master');
            $branch = $repository->createBranch(
                $masterBranch,
                'CLI/AddComposerFileWizard'
            );
            // Checks out the created branch, adds the composer file, commits it and push to it ;)
            $process = new Process('git fetch;git checkout -b ' . $branch->getName() . ';git add composer.json;'
                . ' git commit -m \'[CLI] Added the Composer.json file\';git push -u origin ' . $branch->getName(),
                $this->workingDirectory);
            $process->run();
            if (!$process->isSuccessful()) {
                ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
            }
            // Creates a new pull request
            $pullRequest = $repository->createPullRequest(
                '[CLI] Composer Release',
                'Added the Composer.json file',
                $branch,
                $masterBranch
            );
            // And merges it ;-)
            $pullRequest->merge();
            // And lets remove the remote branch now
            $process = new Process('git push origin --delete ' . $branch->getName(), $this->workingDirectory);
            $process->run();
            if (!$process->isSuccessful()) {
                ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
            }
            // And finally show some success :-)
            $this->io->success('The composer.json file is successfully generated and added to the master branch.');
        }
    }

}