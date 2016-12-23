<?php
namespace AlterNET\Cli\Config;

use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\GeneralUtility;

/**
 * Class LocalConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalConfig extends AbstractConfig
{

    /**
     * LocalConfig constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $filePath = CLI_HOME . '/config.yaml';
        if (!file_exists($filePath)) {
            ConsoleUtility::fileSystem()->touch($filePath);
        }
        parent::__construct(
            GeneralUtility::parseYamlFile(CLI_HOME . '/config.yaml')
        );
    }

    /**
     * Gets the Backup Directory
     *
     * @return string
     */
    public function getBackupDirectory()
    {
        if (isset($this->config['backup']['directory'])) {
            
        }
        return CLI_HOME . '/backups';
    }

}