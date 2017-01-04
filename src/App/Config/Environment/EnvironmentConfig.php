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
     * @var array
     */
    protected $build = [];

    /**
     * @var array
     */
    protected $postBuild = [];

    /**
     * @var array
     */
    protected $buildDatabase = [];

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
        $templates = [
            (isset($environment['template']) ? TemplateUtility::get(strtolower($environment['template']) . '.'
                . strtolower($this->name), TemplateUtility::TYPE_ENVIRONMENT) : []),
            ($defaultEnvironmentConfig ?: []),
            $environment
        ];
        foreach ($templates as $template) {
            foreach (self::getSubjectsToMerge() as $subject => $property) {
                if (isset($template[$subject]) && is_array($template[$subject])) {
                    $this->$property = array_merge($this->$property, $template[$subject]);
                }
                unset($template['subject']);
            }
        }
        $this->environment = array_replace_recursive($templates[0], $templates[1], $templates[2]);
    }

    /**
     * Gets the Application Subjects To Merge array
     *
     * @return array
     * @static
     */
    static protected function getSubjectsToMerge()
    {
        return [
            'build' => 'build',
            'post_build' => 'postBuild',
            'build_database' => 'buildDatabase'
        ];
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
     * Gets the Application Config
     *
     * @return Config
     */
    public function getApplicationConfig()
    {
        return $this->appConfig;
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
     * @return array
     */
    public function getBuilds()
    {
        return $this->build;
    }

    /**
     * Gets the Post Builds
     *
     * @return array
     */
    public function getPostBuilds()
    {
        return $this->postBuild;
    }

    /**
     * Gets the Database Builds
     *
     * @return array
     */
    public function getDatabaseBuilds()
    {
        return $this->buildDatabase;
    }

    /**
     * Is Server
     *
     * @return bool
     */
    public function isServer()
    {
        return (bool)$this->server();
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
            if (isset($this->environment['server'])) {
                $this->server = new ServerConfig($this);
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