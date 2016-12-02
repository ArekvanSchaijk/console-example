<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Command\Bitbucket\BitbucketListCommand;
use AlterNET\Cli\Command\Crowd\CrowdAuthenticateCommand;
use AlterNET\Cli\Command\Hipchat\HipChatListCommand;
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
        $config = Config::load();
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
            new CrowdAuthenticateCommand(),
            new BitbucketListCommand(),
            new HipChatListCommand()
        ];
    }

}