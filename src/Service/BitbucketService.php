<?php
namespace AlterNET\Cli\Service;

use AlterNET\Cli\Config;
use ArekvanSchaijk\BitbucketServerClient\Api;

/**
 * Class BitbucketService
 * @author Arek van Schaijk <info@ucreation.nl>
 */
class BitbucketService
{

    /**
     * @var Api
     */
    protected $api;

    /**
     * BitbucketService constructor.
     */
    public function __construct()
    {
        $config = Config::load();
        $this->api = new Api();
        $this->api->setEndpoint(
            $config->getBitbucketEndpoint()
        );
        $this->api->login(
            $config->getBitbucketUsername(),
            $config->getBitbucketPassword()
        );
    }

    /**
     * Gets the Api
     *
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

}