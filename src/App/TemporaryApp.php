<?php
namespace AlterNET\Cli\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Utility\ConsoleUtility;

/**
 * Class TemporaryApp
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class TemporaryApp extends App
{

    /**
     * TemporaryApp constructor.
     */
    public function __construct()
    {
        $reflect = new \ReflectionClass($this);
        parent::__construct(
            ConsoleUtility::createBuildWorkingDirectory(strtolower($reflect->getShortName()) . '_')
        );
    }

    /**
     * SelfApp destructor.
     */
    public function __destruct()
    {
        $this->remove();
    }

}