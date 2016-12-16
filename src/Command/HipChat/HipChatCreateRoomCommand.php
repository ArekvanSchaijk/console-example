<?php
namespace AlterNET\Cli\Command\HipChat;

use AlterNET\Cli\Command\CommandBase;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Room;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class HipChatListCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class HipChatCreateRoomCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('hipchat:createroom');
        $this->setDescription('Creates a new room');
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the room');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $roomName = trim($input->getArgument('name'));
        // Validates the name arguments
        if (empty($roomName)) {
            throw new \Exception('The room name can\'t be empty');
        }
        // Retrieves the Hipchat authentication token from Cli Config
        $authentication = new OAuth2(self::$config->getHipChatToken());
        // Creates the the Hipchat client
        $client = new Client($authentication);
        // Creates the RoomAPI
        $roomApi = new RoomAPI($client);
        // Creates a new room
        $room = new Room();
        $room->setName($roomName);
        if (($roomId = $roomApi->createRoom($room))) {
            $io->success('The room "' . $roomName . '" (#' . $roomId . ') is succesfully created.');
        }
    }

}