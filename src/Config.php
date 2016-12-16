<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Config\BitbucketConfig;
use AlterNET\Cli\Config\ProjectsConfig;
use AlterNET\Cli\Utility\GeneralUtility;

/**
 * Class Config
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Config
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ProjectsConfig
     */
    protected $projects;

    /**
     * @var BitbucketConfig
     */
    protected $bitbucket;

    /**
     * Projects
     *
     * @return ProjectsConfig
     */
    public function projects()
    {
        if (is_null($this->projects)) {
            $this->projects = new ProjectsConfig($this->config['projects']);
        }
        return $this->projects;
    }

    /**
     * Bitbucket
     *
     * @return BitbucketConfig
     */
    public function bitbucket()
    {
        if (is_null($this->bitbucket)) {
            $this->bitbucket = new BitbucketConfig($this->config['bitbucket']);
        }
        return $this->bitbucket;
    }

    /**
     * Creates the config
     *
     * @return Config
     * @static
     */
    static public function create()
    {
        if (!isset($GLOBALS['ALTERNET_CLI_CONF_VARS'])) {
            $GLOBALS['ALTERNET_CLI_CONF_VARS'] = new Config();
        }
        return $GLOBALS['ALTERNET_CLI_CONF_VARS'];
    }

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->config = GeneralUtility::parseYamlFile(CLI_ROOT . '/config.yaml');
    }

    /**
     * Gets the Application Name
     *
     * @return string
     */
    public function getApplicationName()
    {
        return $this->config['application']['name'];
    }

    /**
     * Gets the Application Version
     *
     * @return string
     */
    public function getApplicationVersion()
    {
        return $this->config['application']['version'];
    }

    /**
     * Gets the HipChat API Token
     *
     * @return string
     */
    public function getHipChatToken()
    {
        return $this->config['hipchat']['token'];
    }

}