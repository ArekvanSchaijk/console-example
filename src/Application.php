<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Command\App\AppBackupCommand;
use AlterNET\Cli\Command\App\AppComposerUpdateCommand;
use AlterNET\Cli\Command\App\AppDomainsCommand;
use AlterNET\Cli\Command\App\AppHostsAddCommand;
use AlterNET\Cli\Command\App\AppHostsDeleteCommand;
use AlterNET\Cli\Command\App\AppRemoveCommand;
use AlterNET\Cli\Command\App\AppShareCommand;
use AlterNET\Cli\Command\Bamboo\BambooListCommand;
use AlterNET\Cli\Command\Bitbucket\BitbucketCreateProjectCommand;
use AlterNET\Cli\Command\Bitbucket\BitbucketCreateRepoCommand;
use AlterNET\Cli\Command\Bitbucket\BitbucketDeleteRepoCommand;
use AlterNET\Cli\Command\Bitbucket\BitbucketListCommand;
use AlterNET\Cli\Command\Bitbucket\BitbucketListReposCommand;
use AlterNET\Cli\Command\HipChat\HipChatCreateRoomCommand;
use AlterNET\Cli\Command\HipChat\HipChatListCommand;
use AlterNET\Cli\Command\HipChat\HipChatListUsersCommand;
use AlterNET\Cli\Command\Local\LocalConfigureCommand;
use AlterNET\Cli\Command\Local\LocalHostsAddCommand;
use AlterNET\Cli\Command\Local\LocalHostsDeleteCommand;
use AlterNET\Cli\Command\Local\LocalIsConnectionCommand;
use AlterNET\Cli\Command\Local\LocalVariablesCommand;
use AlterNET\Cli\Command\App\AppBuildCommand;
use AlterNET\Cli\Command\App\AppEvaluateCommand;
use AlterNET\Cli\Command\App\AppGenerateVhostCommand;
use AlterNET\Cli\Command\App\AppGetCommand;
use AlterNET\Cli\Command\App\AppSyncCommand;
use AlterNET\Cli\Command\Satis\SatisGenerateCommand;
use AlterNET\Cli\Command\Self\SelfReleaseCommand;
use AlterNET\Cli\Command\Self\SelfUpdateCommand;
use AlterNET\Cli\Utility\ConsoleUtility;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

/**
 * Class Application
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Application extends SymfonyConsoleApplication
{

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
        $this->prepare();
    }

    /**
     * Prepare
     * Prepares the console application
     *
     * @return void
     */
    protected function prepare()
    {
        if (!file_exists(CLI_HOME)) {
            ConsoleUtility::fileSystem()->mkdir(CLI_HOME);
        }
        if (!file_exists(CLI_HOME_BUILDS)) {
            ConsoleUtility::fileSystem()->mkdir(CLI_HOME_BUILDS);
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

            // Self

            new SelfReleaseCommand(),
            new SelfUpdateCommand(),

            // Bitbucket
            new BitbucketListCommand(),
            new BitbucketListReposCommand(),
            new BitbucketCreateProjectCommand(),
            new BitbucketCreateRepoCommand(),
            new BitbucketDeleteRepoCommand(),

            // Bamboo
            new BambooListCommand(),

            // HipChat
            new HipChatListCommand(),
            new HipChatListUsersCommand(),
            new HipChatCreateRoomCommand(),

            // Local
            new LocalVariablesCommand(),
            new LocalIsConnectionCommand(),
            new LocalConfigureCommand(),

            // Local Host File
            new LocalHostsAddCommand(),
            new LocalHostsDeleteCommand(),

            // Project
            new AppBuildCommand(),
            new AppEvaluateCommand(),
            new AppShareCommand(),
            new AppGenerateVhostCommand(),
            new AppGetCommand(),
            new AppRemoveCommand(),
            new AppSyncCommand(),
            new AppComposerUpdateCommand(),
            new AppBackupCommand(),
            new AppHostsAddCommand(),
            new AppHostsDeleteCommand(),
            new AppDomainsCommand(),

            // Satis
            new SatisGenerateCommand()
        ];
    }

}