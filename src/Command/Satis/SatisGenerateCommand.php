<?php
namespace AlterNET\Cli\Command\Satis;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Driver\HipChatDriver;
use AlterNET\Cli\Utility\ConsoleUtility;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class SatisGenerateCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class SatisGenerateCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('satis:generate');
        $this->setDescription('Generates the satis.json file.');
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
        // Gets the bitbucket api
        $bitbucket = $this->bitbucketDriver()->getApi();
        // Collect all repositories from projects marked as satis
        $repositories = [];
        foreach ($this->config->bitbucket()->getSatisProjects() as $projectKey) {
            /* @var Repository $repository */
            foreach ($bitbucket->getRepositoriesByProject($projectKey) as $repository) {
                $repositories[] = $repository;
            }
        }
        $satis = $this->config->composer()->getSatisDefaults();
        $workingDirectories = [];
        // Builds all repositories
        foreach ($repositories as $repository) {
            $currentWorkingDirectory = ConsoleUtility::createBuildWorkingDirectory('satisgenerate_');
            $workingDirectories[] = $currentWorkingDirectory;
            $process = new Process('git clone ' . $repository->getSshCloneUrl() . ' .', $currentWorkingDirectory);
            $process->run();
            ConsoleUtility::unSuccessfulProcessExceptionHandler($process, function ($workingDirectories) {
                foreach ($workingDirectories as $workingDirectory) {
                    ConsoleUtility::fileSystem()->remove($workingDirectory);
                }
            });
            // Checks if there is an composer.json file
            if (file_exists($currentWorkingDirectory . '/composer.json')) {
                $contents = file_get_contents($currentWorkingDirectory . '/composer.json');
                try {
                    $json = \GuzzleHttp\json_decode($contents);
                    if (isset($json->name) && !empty(trim($json->name))) {
                        $satis['repositories'][] = $repository->getSshCloneUrl();
                    }
                } catch (\Exception $exception) {
                    $repositoryName = $repository->getProject()->getKey() . '/' . $repository->getName();
                    // Notify about this on HipChat
                    $message = HipChatDriver::createMessage();
                    $message->setColor(Message::COLOR_RED);
                    $message->setMessage('The repository "' . $repositoryName . '" has a invalid composer.json file.');
                    $message->setNotify(true);
                    $message->setFrom('satis:generate');
                    $this->hipChatDriver()->sendMessage($message, $this->config->composer()->getHipChatRoomId());
                    // And share the contents of the file on HipChat
                    $message = HipChatDriver::createMessage();
                    $message->setColor(Message::COLOR_RED);
                    $message->setMessage('/code ' . ($contents ?: '// The file is <empty>'));
                    $message->setFrom('satis:generate');
                    $this->hipChatDriver()->sendMessage($message, $this->config->composer()->getHipChatRoomId());
                    $this->io->warning('The "' . $repositoryName . '"'
                        . ' has a invalid composer.json file.');
                }
            }
            ConsoleUtility::fileSystem()->remove($currentWorkingDirectory);
        }

        print_r($satis);
    }

}