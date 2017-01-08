<?php
namespace AlterNET\Cli\Local\Service;

use AlterNET\Cli\Config;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\GeneralUtility;
use Symfony\Component\Process\Process;

/**
 * Class TemplateService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class TemplateService
{

    const

        TYPE_APPLICATION = 'application',
        TYPE_ENVIRONMENT = 'environment';

    /**
     * TemplateService constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize()
    {
        $this->createDirectoryIfNotExists();
        if ($this->isDirectoryEmpty()) {
            $this->create();
        } elseif (!$this->getTimestamp() || $this->getTimestamp() + $this->getConfig()->getAutoUpdateAfter() <= time()) {
            $this->update();
        }
    }

    /**
     * Gets the Config
     *
     * @return Config\TemplateConfig
     */
    public function getConfig()
    {
        return ConsoleUtility::getConfig()->templates();
    }

    /**
     * Is Directory Empty
     *
     * @return bool
     */
    protected function isDirectoryEmpty()
    {
        foreach (scandir(CLI_HOME_TEMPLATES) as $item) {
            if ($item !== '.' && $item != '..') {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets the Template Path
     *
     * @param string $name
     * @param string $type
     * @return string
     * @static
     */
    static public function getTemplatePath($name, $type)
    {
        return CLI_HOME_TEMPLATES . '/' . $type . '/' . $name . '.yaml';
    }

    /**
     * Template Exists
     *
     * @param string $name
     * @param string $type
     * @return bool
     * @static
     */
    static public function templateExists($name, $type)
    {
        return file_exists(self::getTemplatePath($name, $type));
    }

    /**
     * Creates the templates Directory If not Exists
     *
     * @return void
     */
    protected function createDirectoryIfNotExists()
    {
        if (!file_exists(CLI_HOME_TEMPLATES)) {
            ConsoleUtility::fileSystem()->mkdir(CLI_HOME_TEMPLATES);
        }
    }

    /**
     * Creates the template directory
     *
     * @return void
     */
    public function create()
    {
        $this->createDirectoryIfNotExists();
        // Processes a git clone of the template directory
        $this->process('git clone -b ' . $this->getConfig()->getRemoteBranch() . ' '
            . $this->getConfig()->getRemoteUrl() . ' .');
        // Stores the timestamp into the DataContainer
        $this->storeTimestamp();
    }

    /**
     * Recreates the templates directory
     *
     * @return void
     */
    public function recreate()
    {
        $this->remove();
        $this->initialize();
    }

    /**
     * Updates the templates
     *
     * @return void
     */
    public function update()
    {
        $this->process('git fetch');
        $this->process('git checkout ' . $this->getConfig()->getRemoteBranch());
        $this->process('git pull');
        $this->storeTimestamp();
    }

    /**
     * Retrieves a template
     *
     * @param string $name
     * @param string $type
     * @return array
     * @throws Exception if the template does not exists
     */
    public function retrieve($name, $type)
    {
        if (!self::templateExists($name, $type)) {
            throw new Exception(ucfirst($type) . ' template with name "' . $name . '" does not exists.');
        }
        return (GeneralUtility::parseYamlFile(self::getTemplatePath($name, $type)) ?: []);
    }

    /**
     * Removes the template directory
     *
     * @return void
     */
    public function remove()
    {
        ConsoleUtility::fileSystem()->remove(CLI_HOME_TEMPLATES);
    }

    /**
     * Gets the Timestamp
     *
     * @return int
     */
    protected function getTimestamp()
    {
        return ConsoleUtility::getDataContainer()->getTemplatesTimestamp();
    }

    /**
     * Stores the Timestamp
     *
     * @return void
     */
    protected function storeTimestamp()
    {
        ConsoleUtility::getDataContainer()->setTemplatesTimestamp(time());
        ConsoleUtility::getDataContainer()->write();
    }

    /**
     * Process
     *
     * @param string $commandLine
     * @return void
     */
    protected function process($commandLine)
    {
        $process = new Process($commandLine, CLI_HOME_TEMPLATES);
        $process->run();
        ConsoleUtility::unSuccessfulProcessExceptionHandler($process);
    }

}