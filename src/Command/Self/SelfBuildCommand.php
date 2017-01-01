<?php
namespace AlterNET\Cli\Command\Self;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Creates a new application
        $tempApp = AppUtility::createNewApp($this->config->self()->getRemoteUrl());
        // Gets the highest tag from the repository
        $tag = $tempApp->getGitService()->getHighestTag();
        // Gets the tag related revision
        $tagRevision = $tempApp->getGitService()->getHighestTagRevision();
        // File path
        $filePath = $tempApp->getWebWorkingDirectory() . '/downloads/alternet-' . $tag . '.phar';
        // If the file already exists then we just do nothing ;)
        if (file_exists($filePath)) {
            $this->io->success('Everything is already up to date.');
            exit;
        }
        // Checksout the tags revision
        $tempApp->getGitService()->checkout($tagRevision, false);
        // Builds the revision
        $tempApp->build();

        $this->io->success($filePath);

    }

}