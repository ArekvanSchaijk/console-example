<?php
namespace AlterNET\Cli\App;

use AlterNET\Cli\App\Config\Environment\EnvironmentConfig;
use AlterNET\Cli\App\Config\Environment\EnvironmentSelector;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\GeneralUtility;
use AlterNET\Cli\Utility\TemplateUtility;

/**
 * Class Config
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Config
{

    /**
     * @var array
     */
    protected $config = [];

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
            TemplateUtility::get($this->getApplicationName(), TemplateUtility::TYPE_APPLICATION),
            TemplateUtility::get($this->getApplicationTemplate(), TemplateUtility::TYPE_APPLICATION),
            $this->config
        );
    }

    /**
     * Is Application Template
     *
     * @return bool
     */
    public function isApplicationTemplate()
    {
        return isset($this->config['Application']['template']);
    }

    /**
     * Gets the Application Template
     *
     * @return string
     */
    public function getApplicationTemplate()
    {
        return strtolower($this->config['Application']['template']);
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
     * Gets the HipChat Room Id
     *
     * @return int|bool
     */
    public function getHipChatRoomId()
    {
        return (isset($this->config['Application']['hipchat']) ? (int)$this->config['Application']['hipchat'] : false);
    }

    /**
     * Environment
     *
     * @return EnvironmentSelector
     */
    public function environment()
    {
        if (is_null($this->environment)) {
            $this->environment = new EnvironmentSelector($this->config);
        }
        return $this->environment;
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