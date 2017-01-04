<?php
namespace AlterNET\Cli\Config;

/**
 * Class AppConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppConfig extends AbstractConfig
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
     * Gets the Relative Config File Path
     *
     * @return string
     */
    public function getRelativeConfigFilePath()
    {
        return $this->config['app_config']['relative_file_path'];
    }

    /**
     * Gets the Config Maximum Search Depth
     *
     * @return int
     */
    public function getConfigMaxSearchDepth()
    {
        return (int)$this->config['app_config']['max_search_depth'];
    }

    /**
     * Gets the Relative Working Directory
     *
     * @return string
     */
    public function getRelativeWorkingDirectory()
    {
        return $this->config['relative_working_directory'];
    }

    /**
     * Gets the Relative Local Working Directory
     *
     * @return string
     */
    public function getRelativeLocalWorkingDirectory()
    {
        return $this->config['local']['relative_working_directory'];
    }

}