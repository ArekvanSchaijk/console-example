<?php
namespace AlterNET\Cli\Command\Bamboo;

use AlterNET\Cli\Command\Bamboo\App\BuildApp;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
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
        // Creates the Build application
        $app = new BuildApp(
            $this->getRemoteUrl(),
            $this->getRevision()
        );
        // Tests the application
        $this->testApplication($app);
        // Builds the application
        $app->build(false);
        $documentRoot = $app->getConfig()->current()->server()->getDocumentRoot();
        // Removes the current application if already exists
        if (file_exists($app->getConfig()->current()->server()->getDocumentRoot())) {
            $existingApp = AppUtility::load($documentRoot);
            $existingApp->remove();
        }
        // Moves the application to the document root
        $app->move($documentRoot);
        // Executes the local builds
        $app->buildLocal();
    }

    /**
     * Test Application
     *
     * @param BuildApp $app
     * @return void
     */
    protected function testApplication(BuildApp $app)
    {
        if (!$app->hasConfigFile()) {
            $app->remove();
            $this->fail('The repository has no "' . $this->config->app()->getRelativeConfigFilePath() . '" file.');
        }
        if (!$app->getConfig()->isCurrent()) {
            $app->remove();
            $this->fail('The current environment is not configured.');
        }
        if (!$app->getConfig()->current()->getServerName()) {
            $app->remove();
            $this->fail('The application has no server name configured');
        }
        if (!$app->getConfig()->current()->isServer()) {
            $app->remove();
            $this->fail('The server configuration for the current environment is missing.');
        }
        if (!$app->getConfig()->current()->server()->getDocumentRoot()) {
            $app->remove();
            $this->fail('Cannot resolve the document root since the server has no configuration for it.');
        }
    }

    /**
     * Gets the Remote Url
     *
     * @return string
     */
    protected function getRemoteUrl()
    {
        return $this->input->getArgument('remote_url');
    }

    /**
     * Gets the Revision
     *
     * @return string
     */
    protected function getRevision()
    {
        return $this->input->getArgument('revision');
    }

    /**
     * Failed
     *
     * @param string $message
     * @return void
     */
    protected function fail($message)
    {
        $this->io->error($message);
        exit(1);
    }

}