<?php
namespace AlterNET\Cli\App;

use AlterNET\Cli\App\Config\Environment\EnvironmentConfig;
use AlterNET\Cli\App\Config\Environment\EnvironmentSelector;
use AlterNET\Cli\App\Traits;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Local\Service\TemplateService;
use AlterNET\Cli\Utility\GeneralUtility;
use AlterNET\Package\Environment;

/**
 * Class Config
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Config
{

    use Traits\Local\TemplateServiceTrait;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var EnvironmentSelector
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
        $this->parseConfig();
    }

    /**
     * Gets the Application Subjects To Merge array
     *
     * @return array
     * @static
     */
    static protected function getApplicationSubjectsToMerge()
    {
        return [
            'build' => 'build',
            'post_build' => 'postBuild',
            'build_database' => 'buildDatabase'
        ];
    }

    /**
     * Parses the Templates
     *
     * @return void
     */
    protected function parseConfig()
    {
        $templates = [
            $this->getTemplateService()->retrieve($this->getApplicationName(), TemplateService::TYPE_APPLICATION),
            $this->getTemplateService()->retrieve($this->getApplicationTemplate(), TemplateService::TYPE_APPLICATION),
            $this->config
        ];
        foreach ($templates as $template) {
            foreach (self::getApplicationSubjectsToMerge() as $subject => $property) {
                if (isset($template['application'][$subject]) && is_array($template['application'][$subject])) {
                    $this->$property = array_merge($this->$property, $template['application'][$subject]);
                }
                unset($template['application'][$subject]);
            }
        }
        $this->config = array_replace_recursive($templates[0], $templates[1], $templates[2]);
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
     * Gets the Lower Application Key
     *
     * @return string
     */
    public function getLowerApplicationKey()
    {
        return strtolower($this->getApplicationKey());
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