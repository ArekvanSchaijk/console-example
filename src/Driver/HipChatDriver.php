<?php
namespace AlterNET\Cli\Driver;

use AlterNET\Cli\Config;
use AlterNET\Cli\Utility\ConsoleUtility;
use Buzz\Browser;
use Buzz\Client\Curl;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use GorkaLaucirica\HipchatAPIv2Client\Model\Room;

/**
 * Class HipChatDriver
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class HipChatDriver
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var UserAPI
     */
    protected $userApi;

    /**
     * @var RoomAPI
     */
    protected $roomApi;

    /**
     * BitbucketDriver constructor.
     */
    public function __construct()
    {
        $this->config = ConsoleUtility::getConfig();
        $this->initialize();
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize()
    {
        $client = new Curl();
        $client->setVerifyPeer(false);
        $browser = new Browser($client);
        $this->client = new Client(
            new OAuth2($this->config->getHipChatToken()),
            $browser
        );
    }

    /**
     * Creates a new Message
     *
     * @return Message
     * @static
     */
    static public function createMessage()
    {
        $newMessage = new Message();
        $newMessage->setMessageFormat(Message::FORMAT_TEXT);
        return $newMessage;
    }

    /**
     * User Api
     *
     * @return UserAPI
     */
    protected function userApi()
    {
        if (!$this->userApi) {
            $this->userApi = new UserAPI($this->client);
        }
        return $this->userApi;
    }

    /**
     * Room Api
     *
     * @return RoomAPI
     */
    protected function roomApi()
    {
        if (!$this->roomApi) {
            $this->roomApi = new RoomAPI($this->client);
        }
        return $this->roomApi;
    }

    /**
     * Gets the Rooms
     *
     * @param string|null $search
     * @return Room[]
     */
    public function getRooms($search = null)
    {
        $rooms = [];
        /* @var Room $room */
        foreach ($this->roomApi()->getRooms() as $room) {
            if (!$search || strpos(strtolower($room->getName()), strtolower($search)) !== false) {
                $rooms[] = $room;
            }
        }
        return $rooms;
    }

    /**
     * Gets the Room
     *
     * @param int $roomId
     * @return Room
     */
    public function getRoom($roomId)
    {
        return $this->roomApi()->getRoom($roomId);
    }

    /**
     * Sends a Message
     *
     * @param Message $message
     * @param $roomId
     */
    public function sendMessage(Message $message, $roomId)
    {
        $this->roomApi()->sendRoomNotification($roomId, $message);
    }

}