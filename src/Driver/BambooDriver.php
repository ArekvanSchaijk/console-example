<?php
namespace AlterNET\Cli\Driver;

use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Driver\Bamboo\BambooApi as Api;

/**
 * Class BambooDriver
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BambooDriver
{

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var Config
     */
    protected $config;

    /**
     * BambooDriver constructor.
     * @param CrowdContainer $crowdContainer
     */
    public function __construct(CrowdContainer $crowdContainer)
    {
        $this->config = ConsoleUtility::getConfig();
        $this->initialize($crowdContainer);
    }

    /**
     * Initialize
     *
     * @param CrowdContainer $crowdContainer
     * @return void
     */
    public function initialize(CrowdContainer $crowdContainer)
    {
        $this->api = new Api();
        // Sets the Bamboo endpoint
        $this->api->setEndpoint(
            $this->config->bamboo()->getEndpoint()
        );
        // Logs in with the Crowd credentials
        $this->api->login(
            $crowdContainer->username,
            $crowdContainer->password
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