<?php
namespace AlterNET\Cli\Command\Satis;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Driver\HipChatDriver;
use AlterNET\Cli\Utility\AppUtility;
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
        // Notes that the process can take some time.
        $this->io->note('The satis file will now be generated. This can take some time.');
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
                        $satis['repositories'][] = [
                            'type' => 'vcs',
                            'url' => $repository->getSshCloneUrl()
                        ];
                    }
                } catch (\Exception $exception) {
                    $repositoryName = $repository->getProject()->getKey() . '/' . $repository->getName();
                    // Notify about this on HipChat
                    $message = HipChatDriver::createMessage();
                    $message->setColor(Message::COLOR_RED);
                    $message->setMessage('The repository "' . $repositoryName . '" has an invalid composer.json file.');
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
                        . ' has an invalid composer.json file.');
                }
            }
            ConsoleUtility::fileSystem()->remove($currentWorkingDirectory);
        }
        // Creates a json string of the satis array
        $contents = json_encode($satis, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        // Creates a md5 hash of it
        $contentsHash = md5($contents);
        // Creates a temporary app
        $tempApp = AppUtility::createNewApp($this->config->composer()->getSatisRemoteUrl());
        $satisFilePath = $tempApp->getWorkingDirectory() . '/satis.json';
        $satisInternalFilePath = $tempApp->getWorkingDirectory() . '/internal.json';
        $satisExternalFilePath = $tempApp->getWorkingDirectory() . '/external.json';
        // Checks if the internal.json file exists
        if (file_exists($satisInternalFilePath)) {
            // If the file is not changed then we show some kind of success ;)
            if (md5(file_get_contents($satisInternalFilePath)) === $contentsHash) {
                $this->io->success('Satis internal.json is already up to date.');
                exit;
            }
        }
        // Updates the file
        file_put_contents($satisInternalFilePath, $contents);
        // Generate a new satis.json file
        $external = json_decode(file_get_contents($satisExternalFilePath));
        foreach ($external->repositories as $externalRepository) {
            $satis['repositories'][] = [
                'type' => $externalRepository->type,
                'url' => $externalRepository->url
            ];
        }
        // Creates a json string of the satis array
        $contents = json_encode($satis, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        // Updates the file
        file_put_contents($satisFilePath, $contents);
        // Some kind of status update goes here
        $this->io->note('Pushing it to the server...');
        // Gets the repository object
        $repository =
            $this->bitbucketDriver()->getRepositoryByRemoteUrl($this->config->composer()->getSatisRemoteUrl());
        // Creates a new branch on the server
        $masterBranch = $repository->getBranchByName('master');
        $branch = $repository->createBranch(
            $masterBranch,
            'CLI/UpdateInternalJson'
        );
        // Checks out the created branch, adds the composer file, commits it and push to it ;)
        $tempApp->process('git fetch;git checkout -b ' . $branch->getName() . ';git add internal.json;git add satis.json;'
            . ' git commit -m \'[CLI] Updated the internal.json file\';git push -u origin ' . $branch->getName());
        // Creates a new pull request
        $pullRequest = $repository->createPullRequest(
            '[CLI] Release updated internal.json file',
            'Updated the internal.json file',
            $branch,
            $masterBranch
        );
        // And merges it ;-)
        $pullRequest->merge();
        // And lets remove the remote branch now
        $tempApp->process('git push origin --delete ' . $branch->getName());
        // Clean up the mess ;-)
        $tempApp->remove();
        // Shows some success
        $this->io->success('The Satis internal.json file is successfully updated, pushed and merged.');
    }

}