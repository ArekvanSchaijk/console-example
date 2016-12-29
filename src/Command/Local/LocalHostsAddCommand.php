<?php
namespace AlterNET\Cli\Command\Local;

use AlterNET\Cli\App\Exception;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocalAddHostFileEntryCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalHostsAddCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('local:hostsadd');
        $this->setDescription('Adds an entry to your host file');
        $this->setDescription('Deletes a domain entry to your host file');
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
        // Adds the new entry by asking the user for the input
        $hostFile->addDomain(
            $this->io->ask('Domain'),
            $this->io->ask('IP', '127.0.0.1', function ($value) {
                if (!filter_var($value, FILTER_VALIDATE_IP)) {
                    throw new Exception('"' . $value . '" is not a valid IP address');
                }
                return $value;
            })
        );
        // Writes the host file
        $hostFile->write();
        $this->io->success('The host file is successfully written.');
    }

}