<?php
namespace AlterNET\Cli\Config;

/**
 * Class BambooConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BambooConfig extends AbstractConfig
{

    /**
     * Gets the Endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->config['endpoint'];
    }

}