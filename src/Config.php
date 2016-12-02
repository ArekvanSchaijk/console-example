<?php
namespace AlterNET\Cli;

use Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Config
{

    const

        LOGIN_TEMPLATE_DEFAULT = 'default',
        LOGIN_TEMPLATE_CROWD = 'crowd_template';

    /**
     * @var array
     */
    protected $config;

    /**
     * Load
     *
     * @return mixed
     * @static
     */
    static public function load()
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
        $this->config = Yaml::parse(file_get_contents(CLI_ROOT . '/config.yaml'));
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

    /**
     * Gets the Bitbucket Endpoint
     *
     * @return string
     */
    public function getBitbucketEndpoint()
    {
        return $this->config['bitbucket']['endpoint'];
    }

    /**
     * Gets the Bitbucket Login Template
     *
     * @return string
     */
    public function getBitbucketLoginTemplate()
    {
        if (isset($this->config['bitbucket']['login']) && is_array($this->config['bitbucket']['login'])) {
            return self::LOGIN_TEMPLATE_DEFAULT;
        }
    }

}