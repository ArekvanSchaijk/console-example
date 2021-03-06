<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppDomainsCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppDomainsCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:domains');
        $this->setDescription('Shows the application domains');
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
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();
        if ($app->hasConfigFile()) {
            if (($environments = $app->getConfig()->environment()->all())) {
                foreach ($environments as $environmentConfig) {
                    $this->io->section($environmentConfig->getName());
                    if (($domains = $environmentConfig->getDomains())) {
                        $this->io->listing($domains);
                    } else {
                        $this->io->note('No domains configured for this environment.');
                    }
                }
            } else {
                $this->io->warning('This application has no environments set.');
            }
        }
    }

}