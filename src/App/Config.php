<?php
namespace AlterNET\Cli\App;

use AlterNET\Cli\App\Config\Environment\EnvironmentConfig;
use AlterNET\Cli\App\Config\Environment\EnvironmentSelector;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\GeneralUtility;
use AlterNET\Cli\Utility\TemplateUtility;
use AlterNET\Package\Environment;

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
     * @var EnvironmentSelector
     */
    protected $environment;

    /**
     * AppConfig constructor.
     * @param string $configFilePath
     * @throws Exception
     */
    public function __construct($configFilePath)
    {
        $this->config = GeneralUtility::parseYamlFile($configFilePath);
        if (!$this->isApplicationTemplate()) {
            throw new Exception('The app.conf.yaml does not contain a application template.');
        }
        $this->config = array_replace_recursive(
            TemplateUtility::get($this->getApplicationName(),
                TemplateUtility::TYPE_APPLICATION),
            TemplateUtility::get($this->getApplicationTemplate(),
                TemplateUtility::TYPE_APPLICATION),
            $this->config
        );
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

    /**
     * Is Application Template
     *
     * @return bool
     */
    public function isApplicationTemplate()
    {
        return isset($this->config['application']['template']);
    }

    /**
     * Gets the Application Template
     *
     * @return string
     */
    public function getApplicationTemplate()
    {
        return strtolower($this->config['application']['template']);
    }

    /**
     * Gets the Application Name
     *
     * @return string
     */
    public function getApplicationName()
    {
        return strtok($this->getApplicationTemplate(), '_');
    }

    /**
     * Gets the Application Key
     *
     * @return bool
     */
    public function getApplicationKey()
    {
        if (isset($this->config['application']['key']) && !empty($this->config['application']['key'])) {
            return trim(strtoupper($this->config['application']['key']));
        }
        return false;
    }

    /**
     * Gets the Web Directory
     *
     * @return bool
     */
    public function getWebDirectory()
    {
        if (isset($this->config['application']['web_directory'])) {
            return $this->config['application']['web_directory'];
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
        if (isset($this->config['application']['build']) && is_array($this->config['application']['build'])) {
            return $this->config['application']['build'];
        }
        return false;
    }

    /**
     * Gets the Post Builds
     *
     * @return bool
     */
    public function getPostBuilds()
    {

    }

    public function getDatabaseBuilds()
    {

    }

    public function getPostDatabaseBuilds()
    {

    }

    /**
     * Gets the HipChat Room Id
     *
     * @return int|bool
     */
    public function getHipChatRoomId()
    {
        return (isset($this->config['application']['hipchat']) ? (int)$this->config['application']['hipchat'] : false);
    }

    /**
     * Environment
     *
     * @return EnvironmentSelector
     */
    public function environment()
    {
        if (is_null($this->environment)) {
            $this->environment = new EnvironmentSelector($this);
        }
        return $this->environment;
    }

    /**
     * Is Current
     *
     * @return bool
     */
    public function isCurrent()
    {
        if (Environment::isProductionEnvironment()) {
            return $this->environment()->isProduction();
        } elseif (Environment::isAcceptanceEnvironment()) {
            return $this->environment()->isAcceptance();
        } elseif (Environment::isTestingEnvironment()) {
            return $this->environment()->isTesting();
        } elseif (Environment::isRemoteDevelopmentEnvironment()) {
            return $this->environment()->isDevelopment();
        } elseif (Environment::isLocalEnvironment()) {
            return $this->environment()->isLocal();
        }
        return false;
    }

    /**
     * Current
     *
     * @return EnvironmentConfig
     */
    public function current()
    {
        return $this->environment()->current();
    }

}