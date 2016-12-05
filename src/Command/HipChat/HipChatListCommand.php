<?php
namespace AlterNET\Cli\Command\HipChat;

use AlterNET\Cli\Command\CommandBase;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Room;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class HipChatListCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class HipChatListCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('hipchat:list');
        $this->setDescription('Lists all rooms');
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
        // Creates the RoomAPI
        $roomApi = new RoomAPI($client);
        // Renders the rooms into an array
        $rooms = [];
        /* @var Room $room */
        foreach ($roomApi->getRooms() as $room) {
            $values = [
                $room->getName()
            ];
            if ($this->passItemsThroughFilter($input, $values)) {
                $rooms[] = [
                    $room->getId(),
                    $this->highlightFilteredWords($input, $room->getName())
                ];
            }
        }
        $count = count($rooms);
        $this->renderFilter($input, $output, $count);
        if ($count) {
            $headers = ['#', 'Name'];
            $io->table($headers, $rooms);
        }
    }

}