<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppHostsDeleteCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppHostsDeleteCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:hostsdelete');
        $this->setDescription('Deletes the domains of the application from your host file');
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
        // Asks for confirmation
        if ($this->io->confirm('Are you sure you would like to remove all domains of this application from your'
            . ' host file?', false)
        ) {
            // This loads the app where we are in (working directory)
            $app = AppUtility::load();
            // Do some checks to avoid mistakes
            if (!$app->hasConfigFile()) {
                $this->io->error('The application has no "' . $this->config->app()->getRelativeConfigFilePath() . '" file.');
                exit;
            } elseif (!$app->getConfig()->environment()->isLocal()) {
                $this->io->error('The application has no configuration for your local environment.');
                exit;
            } elseif (!$app->getConfig()->environment()->local()->getDomains()) {
                $this->io->error('The application has no domains configured for your local environment.');
                exit;
            }
            // Deleting the domains from the host file
            foreach ($app->getConfig()->environment()->local()->getDomains() as $domain) {
                $hostFile->removeDomain($domain);
                $this->io->note('Deleting ' . $domain);
            }
            $hostFile->write();
            $this->io->success('The host file is successfully written.');
        } else {
            $this->io->note('Command aborted. Keep calm and carry on.');
        }
    }

}