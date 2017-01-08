<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Command;
use AlterNET\Cli\Utility\ConsoleUtility;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

/**
 * Class Application
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Application extends SymfonyConsoleApplication
{

    /**
     * @var string
     */
    protected static $logo = '  __  _ _____ ___ ___ __  _ ___ _____   __  _   _ 
 /  \| |_   _| __| _ \  \| | __|_   _| |  \| \ / |
| /\ | |_| | | _|| v / | \' | _|  | |   | -<`\ V /\'
|_||_|___|_| |___|_|_\_|\__|___| |_|   |__/  \_/  ' . PHP_EOL;

    /**
     * Gets the help message.
     *
     * @return string A help message
     */
    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $config = Config::create();
        parent::__construct(
            $config->getApplicationName(),
            $config->getApplicationVersion()
        );
        $this->addCommands(
            $this->getCommands()
        );
        $this->createApplicationDirectories();
    }

    /**
     * Gets the Application Directories
     *
     * @return array
     * @static
     */
    static public function getApplicationDirectories()
    {
        return [
            CLI_HOME,
            CLI_HOME_PRIVATE,
            CLI_HOME_BUILDS
        ];
    }

    /**
     * Creates the Application Directories
     *
     * @return void
     */
    protected function createApplicationDirectories()
    {
        foreach (self::getApplicationDirectories() as $directory) {
            if (!file_exists($directory)) {
                ConsoleUtility::getFileSystem()->mkdir($directory);
            }
        }
    }

    /**
     * Gets the Commands
     *
     * @return array
     */
    protected function getCommands()
    {
        return [

            new Command\Self\SelfReleaseCommand(),
            new Command\Self\SelfUpdateCommand(),

            new Command\Bitbucket\BitbucketListCommand(),
            new Command\Bitbucket\BitbucketListReposCommand(),
            new Command\Bitbucket\BitbucketCreateProjectCommand(),
            new Command\Bitbucket\BitbucketCreateRepoCommand(),
            new Command\Bitbucket\BitbucketDeleteRepoCommand(),

            new Command\Bamboo\BambooListCommand(),

            new Command\HipChat\HipChatListCommand(),
            new Command\HipChat\HipChatListUsersCommand(),
            new Command\HipChat\HipChatCreateRoomCommand(),

            new Command\Local\LocalVariablesCommand(),
            new Command\Local\LocalIsConnectionCommand(),
            new Command\Local\LocalConfigureCommand(),
            new Command\Local\LocalHostsAddCommand(),
            new Command\Local\LocalHostsDeleteCommand(),
            new Command\Local\LocalUpdateTemplatesCommand(),

            new Command\App\AppBackupCommand(),
            new Command\App\AppAssistMeCommand(),
            new Command\App\AppShareCommand(),
            new Command\App\AppGetCommand(),
            new Command\App\AppRemoveCommand(),
            new Command\App\AppSyncCommand(),
            new Command\App\AppComposerUpdateCommand(),
            new Command\App\AppBackupCommand(),
            new Command\App\AppHostsAddCommand(),
            new Command\App\AppHostsDeleteCommand(),
            new Command\App\AppDomainsCommand(),
            new Command\App\AppErrorLogCommand(),
            new Command\App\AppBuildCommand(),
            new Command\App\AppBuildLocalCommand(),
            new Command\App\AppBuildApplicationCommand(),
            new Command\App\AppClearLogsCommand(),
            new Command\App\AppEditorCommand(),

            new Command\Satis\SatisGenerateCommand()
        ];
    }

}