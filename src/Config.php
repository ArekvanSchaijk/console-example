<?php
namespace AlterNET\Cli;

use AlterNET\Cli\Config\AppConfig;
use AlterNET\Cli\Config\BambooConfig;
use AlterNET\Cli\Config\BitbucketConfig;
use AlterNET\Cli\Config\ComposerConfig;
use AlterNET\Cli\Config\HostFileConfig;
use AlterNET\Cli\Config\LocalConfig;
use AlterNET\Cli\Config\SelfConfig;
use AlterNET\Cli\Config\TemplateConfig;
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
     * @var SelfConfig
     */
    protected $self;

    /**
     * @var TemplateConfig
     */
    protected $templates;

    /**
     * @var ComposerConfig
     */
    protected $composer;

    /**
     * @var LocalConfig
     */
    protected $local;

    /**
     * @var HostFileConfig
     */
    protected $hostFile;

    /**
     * @var AppConfig
     */
    protected $app;

    /**
     * @var BitbucketConfig
     */
    protected $bitbucket;

    /**
     * @var BambooConfig
     */
    protected $bamboo;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->config = GeneralUtility::parseYamlFile(CLI_ROOT . '/config.yaml');
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
     * Self
     *
     * @return SelfConfig
     */
    public function self()
    {
        if (!$this->self) {
            $this->self = new SelfConfig($this->config['self']);
        }
        return $this->self;
    }

    /**
     * Templates
     *
     * @return TemplateConfig
     */
    public function templates()
    {
        if (!$this->templates) {
            $this->templates = new TemplateConfig($this->config['templates']);
        }
        return $this->templates;
    }

    /**
     * Composer
     *
     * @return ComposerConfig
     */
    public function composer()
    {
        if (!$this->composer) {
            $this->composer = new ComposerConfig($this->config['composer']);
        }
        return $this->composer;
    }

    /**
     * Host File
     *
     * @return HostFileConfig
     */
    public function hostFile()
    {
        if (!$this->hostFile) {
            $this->hostFile = new HostFileConfig($this->config['host_file']);
        }
        return $this->hostFile;
    }

    /**
     * Local
     *
     * @return LocalConfig
     */
    public function local()
    {
        if (!$this->local) {
            $this->local = new LocalConfig();
        }
        return $this->local;
    }

    /**
     * App
     *
     * @return AppConfig
     */
    public function app()
    {
        if (is_null($this->app)) {
            $this->app = new AppConfig($this->config['app']);
        }
        return $this->app;
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
     * Bamboo
     *
     * @return BambooConfig
     */
    public function bamboo()
    {
        if (is_null($this->bamboo)) {
            $this->bamboo = new BambooConfig($this->config['bamboo']);
        }
        return $this->bamboo;
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
     * Gets the Application Server Admin
     *
     * @return string
     */
    public function getApplicationServerAdmin()
    {
        return $this->config['application']['server_admin'];
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
     * Gets the Array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->config;
    }

}