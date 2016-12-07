<?php
namespace AlterNET\Cli\Utility;

/**
 * Class AppUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppUtility
{

    const

        APPLICATION_NAME_TYPO3 = 'typo3';

    /**
     * @var array
     */
    static protected $applicationsNames = [
        self::APPLICATION_NAME_TYPO3
    ];

    /**
     * Gets all Available Application Names
     *
     * @return array
     * @static
     */
    static public function getAvailableApplicationNames()
    {
        return self::$applicationsNames;
    }

}