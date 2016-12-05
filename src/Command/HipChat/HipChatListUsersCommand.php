<?php
namespace AlterNET\Cli\Command\HipChat;

use AlterNET\Cli\Command\CommandBase;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class HipChatListCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class HipChatListUsersCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('hipchat:listusers');
        $this->setDescription('Lists all users');
        $this->addFilterOption();
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Gets the Symfony Style object
        $io = new SymfonyStyle($input, $output);
        // Retrieves the Hipchat authentication token from Cli Config
        $authentication = new OAuth2(self::$config->getHipChatToken());
        // Creates the the Hipchat client
        $client = new Client($authentication);
        // Creates the UserAPI
        $userApi = new UserAPI($client);
        // Renders the users into an array
        $users = [];
        /* @var User $user */
        foreach ($userApi->getAllUsers() as $user) {
            $values = [
                $user->getId(),
                $user->getName(),
                $user->getMentionName()
            ];
            if ($this->passItemsThroughFilter($input, $values)) {
                $users[] = [
                    $user->getId(),
                    $this->highlightFilteredWords($input, $user->getName()),
                    '@' . $this->highlightFilteredWords($input, $user->getMentionName())
                ];
            }
        }
        $count = count($users);
        $this->renderFilter($input, $output, $count);
        if ($count) {
            $headers = ['#', 'Name', 'Mention Name'];
            $io->table($headers, $users);
        }
    }

}