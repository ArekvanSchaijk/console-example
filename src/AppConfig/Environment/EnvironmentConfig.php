<?php
namespace AlterNET\Cli\AppConfig\Environment;

use AlterNET\Cli\Config\AbstractConfig;
use AlterNET\Cli\Utility\TemplateUtility;

/**
 * Class EnvironmentConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class EnvironmentConfig extends AbstractConfig
{

    /**
     * @var string
     */
    protected $name;

    /**
     * EnvironmentConfig constructor.
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->name = $name;
        parent::__construct($config);
        if ($this->isTemplate()) {
            $this->config = array_replace_recursive(
                TemplateUtility::get($this->getTemplate() . '.' . strtolower($this->name),
                    TemplateUtility::TYPE_ENVIRONMENT),
                $this->config
            );
        }
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
        return isset($this->config['template']);
    }

    /**
     * Gets the Template
     *
     * @return string
     */
    public function getTemplate()
    {
        return strtolower($this->config['template']);
    }

    /**
     * Gets the Domains
     *
     * @return array|bool
     */
    public function getDomains()
    {
        if (isset($this->config['Domains']) && count($this->config['Domains'])) {
            return $this->config['Domains'];
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

}