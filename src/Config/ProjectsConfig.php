<?php
namespace AlterNET\Cli\Config;

/**
 * Class ProjectsConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ProjectsConfig extends AbstractConfig
{

    /**
     * Gets the Bitbucket Repository Selectors
     *
     * @return array
     */
    public function getBitbucketRepositorySelectors()
    {
        $selectors = $this->config['bitbucket_repository_selectors'];
        return (is_array($selectors) ? $selectors : []);
    }

    /**
     * Gets the Config File Path
     *
     * @return string
     */
    public function getProjectConfigFilePath()
    {
        return (string)$this->config['config_file_path'];
    }

}