<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\Service\BitbucketService;
use AlterNET\Cli\Service\HipChatService;

/**
 * Class ServiceUtility
 * @author Arek van Schaijk <info@ucreation.nl>
 */
class ServiceUtility
{

    /**
     * @var HipChatService
     */
    static protected $hipChatService;

    /**
     * @var BitbucketService
     */
    static protected $bitbucketService;

    /**
     * Gets the HipChat Service
     *
     * @return HipChatService
     * @static
     */
    static public function getHipChatService()
    {
        if (!self::$hipChatService) {
            self::$hipChatService = new HipChatService();
        }
        return self::$hipChatService;
    }

    /**
     * Gets the Bitbucket Service
     *
     * @return BitbucketService
     * @static
     */
    static public function getBitbucketService()
    {
        if (!self::$bitbucketService) {
            self::$bitbucketService = new BitbucketService();
        }
        return self::$bitbucketService;
    }

}