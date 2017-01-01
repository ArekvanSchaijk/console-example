<?php
namespace AlterNET\Cli\Command\Self;

use AlterNET\Cli\App\SelfBuildApp;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SelfBuildCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class SelfBuildCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('self:build');
        $this->setDescription('Builds the CLI');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // THis gets the bitbucket api
        $bitbucket = $this->bitbucketDriver()->getApi();
        // Gets the 'self', 'build application'
        $app = new SelfBuildApp();
        // If the latest version already exists as download file we just do nothing ;-)
        if (file_exists($app->getNewVersionFilePath())) {
            $app->remove();
            $this->io->success('The latest version (' . $app->getVersion() . ') is already build.');
            exit;
        }
        $this->io->note('Building version ' . $app->getVersion() . '. This can take some time.');
        // Builds the new version
        $app->build();
        // And this releases the new version
        $app->release($this->bitbucketDriver());

    }

}