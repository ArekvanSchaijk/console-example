<?php
namespace AlterNET\Cli\Command\Self;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\ConsoleUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SelfUpdateCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class SelfUpdateCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('self:update');
        $this->setDescription('Updates this cli to the latest version');
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
        // This checks if there is an newer version of the CLI
        if (ConsoleUtility::getSelfService()->isNewVersion()) {
            // This (self) updates the CLI
            $update = ConsoleUtility::getSelfService()->update();
            // Checks if the update was successful
            if ($update === false) {
                $this->io->error('Something went wrong while updating the CLI. Please contact the CLI administrator.');
            } else {
                $this->io->success('The CLI is successfully updated to version: \'' . $update . '\'.');
            }
        } else {
            $this->io->success('The CLI is already up to date.');
        }
    }

}