<?php
namespace AlterNET\Cli\Service;

use AlterNET\Cli\Config;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;

/**
 * Class HipChatService
 * @author Arek van Schaijk <info@ucreation.nl>
 */
class HipChatService
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var RoomAPI
     */
    protected $roomApi;

    /**
     * @var UserAPI
     */
    protected $userApi;

    /**
     * HipChatService constructor.
     */
    public function __construct()
    {
        $config = Config::load();
        $authentication = new OAuth2(
            $config->getHipChatToken()
        );
        $this->client = new Client($authentication);
    }

    /**
     * Gets the HipChat Client
     *
     * @return RoomAPI
     */
    public function getRoomApi()
    {
        if (!$this->roomApi) {
            $this->roomApi = new RoomAPI($this->client);
        }
        return $this->roomApi;
    }

    /**
     * Gets the User Api
     *
     * @return UserAPI
     */
    public function getUserApi()
    {
        if (!$this->userApi) {
            $this->userApi = new UserAPI($this->client);
        }
        return $this->userApi;
    }

}