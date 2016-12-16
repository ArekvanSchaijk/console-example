<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Command\Bitbucket\BitbucketListCommand;
use AlterNET\Cli\Command\Crowd\CrowdAuthenticateCommand;
use AlterNET\Cli\Command\HipChat\HipChatCreateRoomCommand;
use AlterNET\Cli\Command\HipChat\HipChatListCommand;
use AlterNET\Cli\Command\HipChat\HipChatListUsersCommand;
use AlterNET\Cli\Command\Local\LocalIsConnectionCommand;
use AlterNET\Cli\Command\Local\LocalVariablesCommand;
use AlterNET\Cli\Command\App\AppBuildCommand;
use AlterNET\Cli\Command\App\AppEvaluateCommand;
use AlterNET\Cli\Command\App\AppGenerateVhostCommand;
use AlterNET\Cli\Command\App\AppGetCommand;
use AlterNET\Cli\Command\App\AppListCommand;
use AlterNET\Cli\Command\App\AppSyncCommand;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

/**
 * Class AlternetConsole
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AlternetConsole extends SymfonyConsoleApplication
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
            new AppListCommand(),
            new AppBuildCommand(),
            new AppEvaluateCommand(),
            new AppGenerateVhostCommand(),
            new AppGetCommand(),
            new AppSyncCommand()
        ];
    }

}