<?php
namespace AlterNET\Cli\Config;

use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\GeneralUtility;
use AlterNET\Package\Environment;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LocalConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalConfig extends AbstractConfig
{

    const

        OPTION_HOST_FILE_MANAGEMENT = 'host_file_management',
        OPTION_EDITOR_MANAGEMENT = 'editor_management';

    /**
     * @var array
     */
    static public $options = [
        self::OPTION_HOST_FILE_MANAGEMENT
    ];

    /**
     * LocalConfig constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $config = GeneralUtility::parseYamlFile(CLI_DEFAULT_HOME_CONFIG_FILE_PATH);
        if (file_exists(CLI_HOME_CONFIG_FILE_PATH)) {
            $config = array_merge($config, GeneralUtility::parseYamlFile(CLI_HOME_CONFIG_FILE_PATH));
        }
        parent::__construct($config);
    }

    /**
     * Write
     * Writes the local configuration to the home directory
     *
     * @return void
     */
    public function write()
    {
        ConsoleUtility::fileSystem()->touch(CLI_HOME_CONFIG_FILE_PATH);
        file_put_contents(
            CLI_HOME_CONFIG_FILE_PATH,
            Yaml::dump($this->config)
        );
    }

    /**
     * Gets the MySql Username
     *
     * @return string|bool
     */
    public function getMySqlUsername()
    {
        if ($this->config['mysql']['username']) {
            return $this->config['mysql']['username'];
        }
        return $this->getDefaultMySqlUsername();
    }

    /**
     * Gets the Default MySql Username
     *
     * @return string|bool
     */
    public function getDefaultMySqlUsername()
    {
        return 'root';
    }

    /**
     * Sets the MySql Username
     *
     * @param string|bool $username
     * @return void
     */
    public function setMySqlUsername($username)
    {
        $this->config['mysql']['username'] = $username;
    }

    /**
     * Gets the MySql Password
     *
     * @return string|bool
     */
    public function getMySqlPassword()
    {
        if ($this->config['mysql']['password']) {
            return $this->config['mysql']['password'];
        }
        return $this->getDefaultMySqlPassword();
    }

    /**
     * Gets the Default MySql Password
     *
     * @return string
     */
    public function getDefaultMySqlPassword()
    {
        return '';
    }

    /**
     * Sets the MySql Password
     *
     * @param string|bool $password
     * @return void
     */
    public function setMySqlPassword($password)
    {
        $this->config['mysql']['password'] = $password;
    }

    /**
     * Gets the Crowd Username
     *
     * @return string|bool
     */
    public function getCrowdUsername()
    {
        if (Environment::isLocalEnvironment() && $this->config['crowd']['username']) {
            return $this->config['crowd']['username'];
        }
        return $this->getDefaultCrowdUsername();
    }

    /**
     * Gets the Default Crowd Username
     *
     * @return bool
     */
    public function getDefaultCrowdUsername()
    {
        return false;
    }

    /**
     * Sets the Crowd Username
     *
     * @param string $username
     * @return void
     */
    public function setCrowdUsername($username)
    {
        $this->config['crowd']['username'] = $username;
    }

    /**
     * Gets the Crowd Password
     *
     * @return string|bool
     */
    public function getCrowdPassword()
    {
        if (Environment::isLocalEnvironment() && $this->config['crowd']['password']) {
            return $this->config['crowd']['password'];
        }
        return $this->getDefaultCrowdPassword();
    }

    /**
     * Gets the Default Crowd Password
     *
     * @return bool
     */
    public function getDefaultCrowdPassword()
    {
        return false;
    }

    /**
     * Sets the Crowd Password
     *
     * @param string $password
     * @return void
     */
    public function setCrowdPassword($password)
    {
        $this->config['crowd']['password'] = $password;
    }

    /**
     * Gets the Backup Path
     *
     * @return string|bool
     */
    public function getBackupPath()
    {
        if ($this->config['backup']['path']) {
            return $this->config['backup']['path'];
        }
        return $this->getDefaultBackupPath();
    }

    /**
     * Gets the Default Backup Path
     *
     * @return bool
     */
    public function getDefaultBackupPath()
    {
        return false;
    }

    /**
     * Sets the Backup Path
     *
     * @param string|bool $path
     */
    public function setBackupPath($path)
    {
        $this->config['backup']['path'] = $path;
    }

    /**
     * Is Option
     *
     * @param string $name
     * @return bool
     */
    public function isOption($name)
    {
        return isset($this->config['options'][$name]);
    }

    /**
     * Gets an Option
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->config['options'][$name];
    }

    /**
     * Sets a Option's value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->config['options'][$name] = $value;
    }

    /**
     * Is Option
     *
     * @return bool
     */
    public function isOptionHostFileManagement()
    {
        $option = self::OPTION_HOST_FILE_MANAGEMENT;
        if ($this->isOption($option)) {
            return (bool)$this->getOption($option);
        }
        return true;
    }

    /**
     * Is Option Editor Management
     *
     * @return bool
     */
    public function isOptionEditorManagement()
    {
        $option = self::OPTION_EDITOR_MANAGEMENT;
        if ($this->isOption($option)) {
            return (bool)$this->getOption($option);
        }
        return true;
    }

}