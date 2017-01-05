<?php
namespace AlterNET\Cli\App\Config\Environment\Server;

use AlterNET\Cli\App\Config;
use AlterNET\Cli\App\Config\Environment\EnvironmentConfig;
use AlterNET\Cli\Utility\StringUtility;

/**
 * Class ServerConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ServerConfig
{

    const

        DEFAULT_SERVER_PORT = 22;

    /**
     * @var EnvironmentConfig
     */
    protected $environmentConfig;

    /**
     * @var array
     */
    protected $server;

    /**
     * @var string|bool|null
     */
    protected $username;

    /**
     * @var string|bool|null
     */
    protected $password;

    /**
     * @var string|bool|null
     */
    protected $documentRoot;

    /**
     * @var array|null
     */
    protected $build;

    /**
     * @var array|null
     */
    protected $postBuild;

    /**
     * ServerConfig constructor.
     * @param EnvironmentConfig $environmentConfig
     */
    public function __construct(EnvironmentConfig $environmentConfig)
    {
        $this->environmentConfig = $environmentConfig;
        $this->server = $environmentConfig->getArray()['server'];
    }

    /**
     * Gets the Environment Config
     *
     * @return EnvironmentConfig
     */
    public function getEnvironmentConfig()
    {
        return $this->environmentConfig;
    }

    /**
     * Gets the Application Config
     *
     * @return Config
     */
    public function getApplicationConfig()
    {
        return $this->getEnvironmentConfig()->getApplicationConfig();
    }

    /**
     * Gets the Variables
     *
     * @param string $string
     * @return string
     */
    protected function renderVariables($string)
    {
        $variables = [
            'application' => [
                'name' => $this->getApplicationConfig()->getApplicationName(),
                'key' => $this->getApplicationConfig()->getLowerApplicationKey()
            ],
            'environment' => [
                'server_name' => $this->getEnvironmentConfig()->getServerName()
            ],
            'server' => [
                'document_root' => $this->getDocumentRoot()
            ]
        ];
        return StringUtility::parseConfigVariables($string, $variables);
    }

    /**
     * Get Array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->server;
    }

    /**
     * Gets the Host
     *
     * @return string|bool
     */
    public function getHost()
    {
        if (isset($this->server['host'])) {
            return $this->renderVariables((string)$this->server['host']);
        }
        return false;
    }

    /**
     * Gets the Port
     *
     * @return int
     */
    public function getPort()
    {
        return (isset($this->server['port']) ? (int)$this->server['port'] : self::DEFAULT_SERVER_PORT);
    }

    /**
     * Gets the Username
     *
     * @return string|bool
     */
    public function getUsername()
    {
        if (is_null($this->username)) {
            $this->username = false;
            if (isset($this->server['username'])) {
                $this->username = $this->renderVariables((string)$this->server['username']);
            }
        }
        return $this->username;
    }

    /**
     * Gets the Password
     *
     * @return string|bool
     */
    public function getPassword()
    {
        if (is_null($this->password)) {
            $this->password = false;
            if (isset($this->server['password'])) {
                $this->password = $this->renderVariables((string)$this->server['password']);
            }
        }
        return $this->password;
    }

    /**
     * Gets the Document Root
     *
     * @return string|bool
     */
    public function getDocumentRoot()
    {
        if (is_null($this->documentRoot)) {
            $this->documentRoot = false;
            if (isset($this->server['document_root'])) {
                $this->documentRoot = $this->renderVariables((string)$this->server['document_root']);
            }
        }
        return $this->documentRoot;
    }

    /**
     * Gets the Builds
     *
     * @return array
     */
    public function getBuilds()
    {
        if (is_null($this->build)) {
            $this->build = [];
            if (isset($this->server['build']) && is_array($this->server['build'])) {
                foreach ($this->server['build'] as $build) {
                    $this->build[] = $this->renderVariables($build);
                }
            }
        }
        return $this->build;
    }

    /**
     * Gets the Post Builds
     *
     * @return array
     */
    public function getPostBuilds()
    {
        if (is_null($this->postBuild)) {
            $this->postBuild = [];
            if (isset($this->server['post_build']) && is_array($this->server['post_build'])) {
                foreach ($this->server['post_build'] as $build) {
                    $this->postBuild[] = $this->renderVariables($build);
                }
            }
        }
        return $this->postBuild;
    }

}