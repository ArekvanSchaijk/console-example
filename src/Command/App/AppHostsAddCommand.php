<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppHostsAddCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppHostsAddCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:hostsadd');
        $this->setDescription('Adds the domains of the application to your host file');
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
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This prevents the command is being used from outside a local environment
        if (!Environment::isLocalEnvironment()) {
            $this->io->error('This command can only be used from a local environment.');
            exit;
        }
        // Gets the host file service
        $hostFile = ConsoleUtility::getHostFileService();
        // Checks if the host file is writable
        if (!$hostFile->isWritable()) {
            $this->io->error('The host file is not writable. Please run the terminal as root or as Administrator.');
            exit;
        }
        // Check if the host file management is enabled or if it does not ask the user to enable it
        if (!$hostFile->isEnabled()) {
            $this->io->error('Host file management is disabled by config.');
            if ($this->io->confirm('Would you like to enable it?', false)) {
                $hostFile->enable();
            } else {
                $this->io->note('Command aborted.');
                exit;
            }
        }
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();
        // Retrieves the most recent config
        $config = $app->getMostRecentConfig();
        // Do some checks to avoid mistakes
        if (!$config) {
            $this->io->error('The application has no "' . $this->config->app()->getRelativeConfigFilePath() . '" file.');
            exit;
        } elseif (!$config->environment()->isLocal()) {
            $this->io->error('The application has no configuration for your local environment.');
            exit;
        } elseif (!$config->environment()->local()->getDomains()) {
            $this->io->error('The application has no domains configured for your local environment.');
            exit;
        }
        $ip = '127.0.0.1';
        // Adds the domains to the host file
        foreach ($config->environment()->local()->getDomains() as $domain) {
            $hostFile->addDomain($domain, $ip);
            $this->io->note('Adding ' . $domain . ' [' . $ip . ']');
        }
        $hostFile->write();
        $this->io->success('The host file is successfully written.');
    }

}