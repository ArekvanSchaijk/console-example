<?php
namespace AlterNET\Cli\Command\Self;

use AlterNET\Cli\App\SelfBuildApp;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Driver\HipChatDriver;
use AlterNET\Cli\Exception;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
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
        $this->setName('self:release');
        $this->setDescription('Releases the latest version of the CLI');
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
        // This shows some success
        $this->io->success('CLI version "' . $app->getVersion() . '" is successfully released.');
        // Notify about this on HipChat
        $this->io->note('Please wait 10 seconds while we update about it on HipChat....');
        sleep(10);
        $message = HipChatDriver::createMessage()
            ->setMessage('Version ' . $app->getVersion() . ' of the Alternet CLI is just released. Please perform an'
                . ' "alternet self:update" command on your local machine.')
            ->setColor(Message::COLOR_PURPLE)
            ->setNotify(true);
        $this->hipChatDriver()->sendMessage(
            $message,
            $app->getConfig()->getHipChatRoomId()
        );
        $this->io->success('All things done. Have a nice day ;-)');
    }

}