<?php
namespace AlterNET\Cli\Command\Bitbucket;

use AlterNET\Cli\Command\Bitbucket\App\CreateRepoApp;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\StringUtility;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        // This creates the temporary app
        $app = new CreateRepoApp();
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
        // And finally creates the new repository through the bitbucket API and clones it into the app
        // A friendly note: the exception handling is done by the api itself
        $app->setRepository(
            $project->createRepository($repository)
        );
        $app->git()->cloneUrl($app->getRepository()->getSshCloneUrl());
        $this->io->success('The repository has been created successfully.');
        // Runs the "execute" options for this repository
        foreach ($options as $option) {
            switch ($option) {

                case self::EXECUTE_HIPCHAT_INTEGRATION:
                    $this->executeHipChatIntegration($app);
                    break;

                case self::EXECUTE_CREATE_MASTER_BRANCH:
                    $this->executeCreateMasterBranch($app);
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
                    $this->wizardCreateComposerFile($app);
                    break;

                default:
                    // Do nothing here :)
                    break;
            }
        }
        // Runs the bitbucket:listrepos command with a --filter which displays the created repository
        $this->runCommand('bitbucket:listrepos', [
            'project' => $app->getProjectKey(),
            '--filter' => $app->getRepository()->getName(),
            '--filter-no-count'
        ]);
    }

    /**
     * Executes the HipChat Integration
     *
     * @param CreateRepoApp $app
     * @return void
     */
    protected function executeHipChatIntegration(CreateRepoApp $app)
    {
        if (($roomId = $this->config->bitbucket()->getProjectHipChatRoomId($app->getProjectKey()))) {
            try {
                $app->getRepository()->createHipChatIntegration($roomId);
                $this->io->success('The repository has been integrated with HipChat successfully.');
            } catch (\Exception $exception) {
                $this->io->error('Could not create the HipChat integration.');
            }
        }
    }

    /**
     * Executes the Creation of the Master Branch
     *
     * @param CreateRepoApp $app
     * @return void
     */
    protected function executeCreateMasterBranch(CreateRepoApp $app)
    {
        $app->filePutContents($app->touch('master'), date('d-m-Y H:i:s'));
        $app->git()->add();
        $app->git()->commit('[CLI] Initial commit of master.txt');
        $app->git()->push('master');
        $this->io->success('The "master" branch has been created successfully.');
    }

    /**
     * Wizard Create Composer File
     *
     * @param CreateRepoApp $app
     * @return void
     */
    protected function wizardCreateComposerFile(CreateRepoApp $app)
    {
        if ($this->io->confirm('Do you want to create a Composer.json file right away?')) {
            $composer = [];
            // Gets the Composer configuration belonging to this repository
            $config = $this->config->bitbucket()->getProjectComposerConfig($app->getProjectKey());
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
            $composer['name'] = $vendor . '/' . $app->getRepository()->getName();
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
            $app->filePutContents(
                $app->touch('composer.json'),
                json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
            // Notes that the process can take some time.
            $this->io->note('The file will now be generated, pushed and merged. This can take some time.');
            // Create a new branch on the server
            $masterBranch = $app->getRepository()->getBranchByName('master');
            $branch = $app->getRepository()->createBranch(
                $masterBranch,
                'CLI/AddComposerFileWizard'
            );
            // Checks out the created branch, adds the composer file, commits it and push to it ;)
            $app->git()->fetch();
            $app->git()->checkout($branch->getName(), false);
            $app->git()->add('composer.json');
            $app->git()->commit('[CLI] Added default Composer.json file');
            $app->git()->push($branch->getName());
            // Creates a new pull request
            $pullRequest = $app->getRepository()->createPullRequest(
                '[CLI] Composer Release',
                'Added the Composer.json file',
                $branch,
                $masterBranch
            );
            // And merges it ;-)
            $pullRequest->merge();
            // And lets remove the remote branch now
            $app->git()->deleteRemoteBranch($branch->getName());
            // And finally show some success :-)
            $this->io->success('The composer.json file is successfully generated and added to the master branch.');
        }
    }

}