<?php
namespace AlterNET\Cli\Command\Crowd;

use AlterNET\Cli\Command\CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CrowdAuthenticateCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class CrowdAuthenticateCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('crowd:authenticate');
        $this->setDescription('Authenticates the ' . self::$config->getApplicationName());
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
        $this->processCrowdLogin($input, $output);
    }

}