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
     * @param string|null $documentRoot
     * @param array|null $domains
     * @param string|null $errorLogPath
     * @param string|null $accessLogPath
     * @param string|null $serverAdmin
     * @param bool $redirectToHttps
     * @return string
     * @static
     */
    static public function generateVirtualHostString(
        $port = 80,
        $documentRoot = null,
        array $domains = null,
        $errorLogPath = null,
        $accessLogPath = null,
        $serverAdmin = null,
        $redirectToHttps = false
    )
    {
        $string = '<VirtualHost *:' . $port . '>' . PHP_EOL;
        if ($domains) {
            $i = 0;
            foreach ($domains as $domain) {
                switch ($i) {
                    case 0:
                        $string .= chr(9) . 'ServerName ' . $domain . PHP_EOL;
                        break;
                    default:
                        $string .= chr(9) . 'ServerAlias ' . $domain . PHP_EOL;
                }
                $i++;
            }
        }
        if ($serverAdmin && filter_var($serverAdmin, FILTER_VALIDATE_EMAIL)) {
            $string .= chr(9) . 'ServerAdmin ' . $serverAdmin . PHP_EOL;
        }
        if ($documentRoot) {
            $string .= chr(9) . 'DocumentRoot "' . realpath($documentRoot) . '"' . PHP_EOL;
        }
        if ($errorLogPath) {
            $string .= chr(9) . 'ErrorLog "' . realpath($errorLogPath) . '"' . PHP_EOL;
        }
        if ($accessLogPath) {
            $string .= chr(9) . 'CustomLog "' . realpath($accessLogPath) . '" common' . PHP_EOL;
        }
        if ($redirectToHttps) {
            $string .= chr(9) . 'RewriteEngine On' . PHP_EOL;
            $string .= chr(9) . 'RewriteCond %{HTTPS} off' . PHP_EOL;
            $string .= chr(9) . 'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI}' . PHP_EOL;
        }
        $string .= '</VirtualHost>';
        return $string;
    }

}