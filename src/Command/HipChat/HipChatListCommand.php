<?php
namespace AlterNET\Cli\Command\HipChat;

use AlterNET\Cli\Command\CommandBase;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Room;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Retrieves the Hipchat authentication token from Cli Config
        $authentication = new OAuth2($this->config->getHipChatToken());
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
            if ($this->passItemsThroughFilter($values)) {
                $rooms[] = [
                    $room->getId(),
                    $this->highlightFilteredWords($room->getName())
                ];
            }
        }
        $count = count($rooms);
        $this->renderFilter($count);
        if ($count) {
            $headers = ['#', 'Name'];
            $this->io->table($headers, $rooms);
        }
    }

}