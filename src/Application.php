<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Command\Bitbucket\BitbucketListCommand;
use AlterNET\Cli\Command\Crowd\CrowdAuthenticateCommand;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

/**
 * Class Application
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Application extends SymfonyConsoleApplication
{

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
            new BitbucketListCommand()
        ];
    }

}