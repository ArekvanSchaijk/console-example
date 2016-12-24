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

}