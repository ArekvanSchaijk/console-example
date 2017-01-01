<?php
namespace AlterNET\Cli\Config;

/**
 * Class SelfConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class SelfConfig extends AbstractConfig
{

    /**
     * Gets the Remote Url
     *
     * @return string
     */
    public function getRemoteUrl()
    {
        return $this->config['remote_url'];
    }

    /**
     * Gets the Manifest Url
     *
     * @return string
     */
    public function getManifestUrl()
    {
        return $this->config['manifest_url'];
    }

}