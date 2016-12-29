<?php
namespace AlterNET\Cli\Command\Local;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocalHostsDeleteCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalHostsDeleteCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('local:hostsdelete');
        $this->setDescription('Deletes a domain entry from your host file');
        $this->addArgument('domain', InputArgument::OPTIONAL, 'The domain name to delete.');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
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
        $domain = null;
        // Gets the entries
        $domains = $hostFile->getEntries();
        if (($domain = $input->getArgument('domain'))) {
            if (!isset($domains[$domain])) {
                $domain = null;
                $this->io->error('The host file has no entry for domain "' . $domain . '".');
            }
        }
        // This offers a list with domains to choose out
        if (!$domain) {
            $domain = $this->io->choice('Which domain would you like to delete from your host file?', $domains);
        }
        // Asks for confirmation
        if ($this->io->confirm('Are you sure you would like to delete "' . $domain . '" from your host file?', false)) {
            $hostFile->removeDomain($domain);
            $hostFile->write();
            $this->io->success('The domain "' . $domain . '" is successfully removed.');
        } else {
            $this->io->note('Command aborted.');
        }
    }

}