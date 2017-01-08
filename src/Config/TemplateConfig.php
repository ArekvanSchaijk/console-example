<?php
namespace AlterNET\Cli\Config;

/**
 * Class TemplateConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class TemplateConfig extends AbstractConfig
{

    /**
     * Get The Remote Url
     *
     * @return string
     */
    public function getRemoteUrl()
    {
        return $this->config['remote_url'];
    }

    /**
     * Gets the Remote Branch
     *
     * @return string
     */
    public function getRemoteBranch()
    {
        return $this->config['remote_branch'];
    }

    /**
     * Gets the Auto Update After
     *
     * @return int
     */
    public function getAutoUpdateAfter()
    {
        return $this->config['auto_update_after'];
    }

}