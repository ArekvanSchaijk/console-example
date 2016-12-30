<?php
namespace AlterNET\Cli\Config;

/**
 * Class ComposerConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ComposerConfig extends AbstractConfig
{

    /**
     * Gets the Default Vendor
     *
     * @return string
     */
    public function getDefaultVendor()
    {
        return $this->config['default_vendor'];
    }

    /**
     * Gets the Available Licenses
     *
     * @return array
     */
    public function getAvailableLicenses()
    {
        return $this->config['available_licenses'];
    }

    /**
     * Gets the Hip Chat RoomId
     *
     * @return int
     */
    public function getHipChatRoomId()
    {
        return (int)$this->config['hipchat'];
    }

    /**
     * Gets the Satis Remote Url
     *
     * @return string
     */
    public function getSatisRemoteUrl()
    {
        return $this->config['satis']['remote_url'];
    }

    /**
     * Gets the Satis Defaults
     *
     * @return array
     */
    public function getSatisDefaults()
    {
        return $this->config['satis']['defaults'];
    }

}