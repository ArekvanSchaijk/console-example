<?php
namespace AlterNET\Cli\Command\Bamboo\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Utility\ConsoleUtility;

/**
 * Class BuildApp
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BuildApp extends App
{

    /**
     * TemporaryApp constructor.
     * @param string $remoteUrl
     * @param string $revision
     */
    public function __construct($remoteUrl, $revision)
    {
        $reflect = new \ReflectionClass($this);
        parent::__construct(
            ConsoleUtility::createBuildWorkingDirectory(strtolower($reflect->getShortName()) . '_')
        );
        $this->git()->cloneUrl($remoteUrl);
        $this->git()->checkout($revision, false);
    }
    
}