<?php
namespace AlterNET\Cli\Utility;

/**
 * Class ApacheUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ApacheUtility
{

    /**
     * Generates the Virtual Host String
     *
     * @param int $port
     * @param string $documentRoot
     * @param array $domains
     * @param string $errorLogPath
     * @param string $accessLogPath
     * @param string|bool $serverAdmin
     * @return string
     * @static
     */
    static public function generateVirtualHostString(
        $port = 80,
        $documentRoot,
        array $domains,
        $errorLogPath,
        $accessLogPath,
        $serverAdmin = false
    )
    {
        $string = '<VirtualHost *:' . $port . '>' . PHP_EOL;
        if ($serverAdmin && filter_var($serverAdmin, FILTER_VALIDATE_EMAIL)) {
            $string .= chr(9) . 'ServerAdmin' . chr(9) . '"' . $serverAdmin . '"' . PHP_EOL;
        }
        $string .= chr(9) . 'DocumentRoot' . chr(9) . '"' . realpath($documentRoot) . '"' . PHP_EOL;
        $i = 0;
        foreach ($domains as $domain) {
            switch ($i) {
                case 0:
                    $string .= chr(9) . 'ServerName' . chr(9) . $domain . PHP_EOL;
                    break;
                default:
                    $string .= chr(9) . 'ServerAlias' . chr(9) . $domain . PHP_EOL;
            }
            $i++;
        }
        $string .= chr(9) . 'ErrorLog' . chr(9) . '"' . realpath($errorLogPath) . '"' . PHP_EOL;
        $string .= chr(9) . 'CustomLog' . chr(9) . '"' . realpath($accessLogPath) . '" common' . PHP_EOL;
        $string .= '</VirtualHost>';
        return $string;
    }

}