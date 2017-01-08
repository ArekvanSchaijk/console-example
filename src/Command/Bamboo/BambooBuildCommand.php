<?php
namespace AlterNET\Cli\Command\Bamboo;

use AlterNET\Cli\Command\CommandBase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BambooBuildEnvironmentCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BambooBuildCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bamboo:build');
        $this->setDescription('Builds a repository by a bamboo build');
        $this->addArgument('remote_url', InputArgument::REQUIRED, 'The remote url of the repository to build');
        $this->addArgument('revision', InputArgument::REQUIRED, 'The revision to build');
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
        $this->output->writeln($this->input->getArgument('remote_url'));
        $this->output->writeln($this->input->getArgument('revision'));
    }

}