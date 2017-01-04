<?php
namespace AlterNET\Cli\App\Config\Environment;

use AlterNET\Cli\App\Config;
use AlterNET\Cli\App\Config\Environment\Server\ServerConfig;
use AlterNET\Cli\Utility\TemplateUtility;

/**
 * Class EnvironmentConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class EnvironmentConfig
{

    const

        DEFAULT_HTTP_PORT = 80;

    const

        OPTION_HTTP_PORT = 'http_port',
        OPTION_SSL_PORT = 'ssl_port',
        OPTION_FORCE_HTTPS = 'force_https';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ServerConfig|bool
     */
    protected $server;

    /**
     * @var Config
     */
    protected $appConfig;

    /**
     * @var array
     */
    protected $environment;

    /**
     * EnvironmentConfig constructor.
     * @param Config $appConfig
     * @param string $name
     * @param array $environment
     * @param array $defaultEnvironmentConfig
     */
    public function __construct(Config $appConfig, $name, array $environment, array $defaultEnvironmentConfig = null)
    {
        $this->name = $name;
        $this->appConfig = $appConfig;
        $this->environment = $environment;
        if ($this->isTemplate()) {
            $this->environment = array_replace_recursive(
                TemplateUtility::get($this->getTemplate() . '.' . strtolower($this->name),
                    TemplateUtility::TYPE_ENVIRONMENT),
                $this->environment
            );
        }
        if ($defaultEnvironmentConfig) {
            $this->environment = array_replace_recursive($defaultEnvironmentConfig, $this->environment);
        }
    }

    /**
     * Gets the Array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->environment;
    }

    /**
     * Gets the Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Is Template
     *
     * @return bool
     */
    public function isTemplate()
    {
        return isset($this->environment['template']);
    }

    /**
     * Gets the Template
     *
     * @return string
     */
    public function getTemplate()
    {
        return strtolower($this->environment['template']);
    }

    /**
     * Gets the Domains
     *
     * @return array|bool
     */
    public function getDomains()
    {
        if (isset($this->environment['domains']) && count($this->environment['domains'])) {
            return $this->environment['domains'];
        }
        return false;
    }

    /**
     * Gets the Server Name
     *
     * @return string|bool
     */
    public function getServerName()
    {
        if (($domains = $this->getDomains())) {
            return array_values($domains)[0];
        }
        return false;
    }

    /**
     * Gets the Git Branch
     *
     * @return string|bool
     */
    public function getGitBranch()
    {
        if (isset($this->environment['git_branch'])) {
            return $this->environment['git_branch'];
        }
        return false;
    }

    /**
     * Gets the Builds
     *
     * @return array|bool
     */
    public function getBuilds()
    {
        if (isset($this->environment['build']) && is_array($this->environment['build'])) {
            return $this->environment['build'];
        }
        return false;
    }

    /**
     * Is Server
     *
     * @return bool
     */
    public function isServer()
    {
        return isset($this->environment['server']);
    }

    /**
     * Server
     *
     * @return ServerConfig|bool
     */
    public function server()
    {
        if (is_null($this->server)) {
            $this->server = false;
            if ($this->isServer()) {
                $this->server = new ServerConfig($this->environment['server']);
            }
        }
        return $this->server;
    }

    /**
     * Gets the Options
     *
     * @return array
     */
    public function getOptions()
    {
        if (isset($this->environment['options']) && is_array($this->environment['options'])) {
            return $this->environment['options'];
        }
        return [];
    }

    /**
     * Is Option
     *
     * @param string $name
     * @return bool
     */
    public function isOption($name)
    {
        return (isset($this->getOptions()[$name]));
    }

    /**
     * Gets an Option
     *
     * @param string $name
     * @return bool|mixed
     */
    public function getOption($name)
    {
        return (isset($this->getOptions()[$name]) ? $this->getOptions()[$name] : false);
    }

    public function getListenPort()
    {

    }

    /**
     * Gets the Http Port
     *
     * @return int
     */
    public function getHttpPort()
    {
        if ($this->isOption(self::OPTION_HTTP_PORT)) {
            return (int)$this->getOption(self::OPTION_HTTP_PORT);
        }
        return self::DEFAULT_HTTP_PORT;
    }

    /**
     * Is Use Ssl
     *
     * @return bool
     */
    public function isSsl()
    {
        return $this->isOption(self::OPTION_SSL_PORT);
    }

    /**
     * Gets the Ssl Port
     *
     * @return int|bool
     */
    public function getSslPort()
    {
        if ($this->isOption(self::OPTION_SSL_PORT)) {
            return (int)$this->getOption(self::OPTION_SSL_PORT);
        }
        return false;
    }

    /**
     * Is Force Https
     *
     * @return bool
     */
    public function isForceHttps()
    {
        if ($this->isOption(self::OPTION_FORCE_HTTPS)) {
            return (bool)$this->getOption(self::OPTION_FORCE_HTTPS);
        }
        return false;
    }

}