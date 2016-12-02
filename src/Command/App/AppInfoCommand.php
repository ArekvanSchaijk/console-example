<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\AppCommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppInfoCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppInfoCommand extends AppCommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('app:info');
        $this->setDescription('Displays info about the current application');
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

    }

}