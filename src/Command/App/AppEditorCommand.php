<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\StringUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppEditorCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppEditorCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('app:editor');
        $this->setDescription('Opens the project into an editor');
        $this->addArgument('file', InputArgument::OPTIONAL, 'The path to the file to be opened.');
    }

    /**
     * Executes the command
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
        // Checks if the editor management is enabled
        if (!$app->editor()->isEnabled()) {
            $this->io->error('Editor management is disabled by config.');
            if ($this->io->confirm('Would you like to enable it?', false)) {
                $app->editor()->enable();
            } else {
                $this->io->note('Command aborted.');
                exit;
            }
        }
        // This checks if the user is working on a local machine
        if (!Environment::isLocalEnvironment()) {
            $this->io->error('This command can only be used on a local machine.');
        } else {
            // And finally opens the editor
            if ($app->editor()->open(($this->input->getArgument('file') ?: null))) {
                $this->io->success('The editor should be launch soon.');
            } else {
                $this->io->warning('This feature is not supported (yet) on your environment.');
            }
        }
    }

    /**
     * Gets the File to be opened
     *
     * @param App $app
     * @return string
     */
    protected function getFile(App $app)
    {
        if (($file = $this->input->getArgument('file'))) {
            if (StringUtility::isAbsolutePath($file)) {
                return $app->getWorkingDirectory() . $file;
            }
            return $file;
        }
        return '.';
    }

}