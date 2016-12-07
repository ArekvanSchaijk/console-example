<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Command\Bitbucket\BitbucketListCommand;
use AlterNET\Cli\Command\Crowd\CrowdAuthenticateCommand;
use AlterNET\Cli\Command\HipChat\HipChatCreateRoomCommand;
use AlterNET\Cli\Command\HipChat\HipChatListCommand;
use AlterNET\Cli\Command\HipChat\HipChatListUsersCommand;
use AlterNET\Cli\Command\Local\LocalIsConnectionCommand;
use AlterNET\Cli\Command\Local\LocalVariablesCommand;
use AlterNET\Cli\Command\Project\ProjectBuildCommand;
use AlterNET\Cli\Command\Project\ProjectEvaluateCommand;
use AlterNET\Cli\Command\Project\ProjectGenerateVhostCommand;
use AlterNET\Cli\Command\Project\ProjectGetCommand;
use AlterNET\Cli\Command\Project\ProjectListCommand;
use AlterNET\Cli\Command\Project\ProjectSyncCommand;
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
    }

    /**
     * Gets the Commands
     *
     * @return array
     */
    public function getCommands()
    {
        return [

            // Crowd
            new CrowdAuthenticateCommand(),

            // Bitbucket
            new BitbucketListCommand(),

            // HipChat
            new HipChatListCommand(),
            new HipChatListUsersCommand(),
            new HipChatCreateRoomCommand(),

            // Local
            new LocalVariablesCommand(),
            new LocalIsConnectionCommand(),

            // Project
            new ProjectListCommand(),
            new ProjectBuildCommand(),
            new ProjectEvaluateCommand(),
            new ProjectGenerateVhostCommand(),
            new ProjectGetCommand(),
            new ProjectSyncCommand()
        ];
    }

}