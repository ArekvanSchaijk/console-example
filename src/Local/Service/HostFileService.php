<?php
namespace AlterNET\Cli\Local\Service;

use AlterNET\Cli\Exception;
use AlterNET\Cli\Config;
use AlterNET\Cli\Config\LocalConfig;
use AlterNET\Cli\Utility\ConsoleUtility;

/**
 * Class HostFileService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class HostFileService
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    static protected $markerStart = '### ALTERNET_CLI START ###';

    /**
     * @var string
     */
    static protected $markerEnd = '### ALTERNET_CLI END ###';

    /**
     * @var array
     */
    protected $entries = [];

    /**
     * @var array
     */
    protected $hostFiles = [
        '/etc/hosts',
        '/private/etc/hosts',
        'C:\Windows\System32\drivers\etc\hosts'
    ];

    /**
     * @var string|bool|null
     */
    protected $path;

    /**
     * HostFileService constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if (!$this->getHostFilePath()) {
            throw new Exception('Could not find the host file location.');
        }
        $this->config = ConsoleUtility::getConfig();
        $this->initialize();
    }

    /**
     * Is Writable
     *
     * @return bool
     */
    public function isWritable()
    {
        if ($this->getHostFilePath()) {
            return is_writable($this->getHostFilePath());
        }
        return false;
    }

    /**
     * Create
     *
     * @return HostFileService
     * @static
     */
    static public function create()
    {
        if (!isset($GLOBALS['ALTERNET_CLI_HOST_FILE_SERVICE'])
            || !$GLOBALS['ALTERNET_CLI_HOST_FILE_SERVICE'] instanceof HostFileService
        ) {
            $GLOBALS['ALTERNET_CLI_HOST_FILE_SERVICE'] = new HostFileService();
        }
        return $GLOBALS['ALTERNET_CLI_HOST_FILE_SERVICE'];
    }

    /**
     * Gets the Host File Path
     *
     * @return string
     */
    protected function getHostFilePath()
    {
        if (is_null($this->path)) {
            $this->path = false;
            foreach ($this->hostFiles as $hostFile) {
                if (file_exists($hostFile)) {
                    $this->path = $hostFile;
                    break;
                }
            }
        }
        return $this->path;
    }

    /**
     * Gets the Contents
     *
     * @return string
     */
    protected function getContents()
    {
        return trim(file_get_contents($this->getHostFilePath()));
    }

    /**
     * Has Markers
     *
     * @param string $contents
     * @return bool
     */
    protected function hasMarkers($contents)
    {
        if (strpos($contents, self::$markerStart) !== FALSE && strpos($contents, self::$markerEnd) !== FALSE) {
            return true;
        }
        return false;
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize()
    {
        // Gets the host file content
        $contents = $this->getContents();
        // Checks if the markers exists
        if ($this->hasMarkers($contents)) {
            preg_match_all('/' . self::$markerStart . '(.*?)' . self::$markerEnd . '/s', $contents, $matches);
            if (isset($matches[1][0])) {
                // Reads the string
                $string = trim($matches[1][0]);
                if (!empty($string)) {
                    foreach (explode(PHP_EOL, $string) as $line) {
                        list($ip, $domain) = preg_split('/\s+/', $line);;
                        $this->entries[$domain] = $ip;
                    }
                }
            }
        }
    }

    /**
     * Gets the host file entries
     *
     * @return array
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Is Domain
     *
     * @param string $domain
     * @return bool
     */
    public function isDomain($domain)
    {
        return isset($this->entries[$domain]);
    }

    /**
     * Adds a Domain
     *
     * @param string $domain
     * @param string $ip
     * @throws Exception
     */
    public function addDomain($domain, $ip)
    {
        $ip = trim($ip);
        $domain = trim($domain);
        if (empty($domain) || empty($ip)) {
            throw new Exception('The domain or IP cannot be empty.');
        } elseif (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception('"' . $ip . '" is not a valid IP address');
        }
        $this->entries[trim($domain)] = trim($ip);
    }

    /**
     * Removes a Domain
     *
     * @param string $domain
     */
    public function removeDomain($domain)
    {
        unset($this->entries[$domain]);
    }

    /**
     * Is Enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->local()->isOptionHostFileManagement();
    }

    /**
     * Enable
     *
     * @return void
     */
    public function enable()
    {
        $this->config->local()->setOption(LocalConfig::OPTION_HOST_FILE_MANAGEMENT, true);
        $this->config->local()->write();
    }

    /**
     * Disable
     *
     * @return void
     */
    public function disable()
    {
        $this->config->local()->setOption(LocalConfig::OPTION_HOST_FILE_MANAGEMENT, false);
        $this->config->local()->write();
    }

    /**
     * Writes the host file
     *
     * @return void
     * @throws Exception
     */
    public function write()
    {
        if (!$this->isWritable()) {
            throw new Exception('The host file is not writable.');
        }
        // Creates the new alternet_cli string
        $string = self::$markerStart . PHP_EOL;
        foreach ($this->getEntries() as $domain => $ip) {
            $string .= $ip . chr(9) . $domain . PHP_EOL;
        }
        $string .= self::$markerEnd;
        // Gets the host file content
        $contents = $this->getContents();
        // If the current file does not have markers yet
        if (!$this->hasMarkers($contents)) {
            $contents .= PHP_EOL . PHP_EOL . $string;
        } else {
            $contents = preg_replace('/' . self::$markerStart . '(.*?)' . self::$markerEnd . '/s', $string, $this->getContents());
        }
        file_put_contents($this->getHostFilePath(), $contents);
    }

}