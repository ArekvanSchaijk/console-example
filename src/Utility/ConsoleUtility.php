<?php
namespace AlterNET\Cli\Utility;

/**
 * Class ConsoleUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ConsoleUtility
{

    /**
     * Is Internet Connection
     * Checks if there is an internet connection (or not)
     *
     * @return bool
     * @static
     */
    static public function isInternetConnection()
    {
        return (bool)@fsockopen('www.google.com', 80, $num, $error, 5);
    }

}