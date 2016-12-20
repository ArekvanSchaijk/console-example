<?php
namespace AlterNET\Cli\Driver;

use AlterNET\Cli\Config;
use AlterNET\Cli\Utility\ConsoleUtility;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

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
        $this->client = new Client(
            new OAuth2($this->config->getHipChatToken())
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
        return new Message();
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