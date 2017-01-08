<?php
namespace AlterNET\Cli\Command\Self;

use AlterNET\Cli\Command\CommandBase;
use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
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
        $manager = new Manager(Manifest::loadFile($this->config->self()->getManifestUrl()));
        if ($manager->update($this->getApplication()->getVersion())) {
            $this->io->success('The CLI is successfully updated.');
        } else {
            $this->io->error('Could not update the CLI');
        }
    }

}