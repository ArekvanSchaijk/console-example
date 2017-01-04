<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GeneralUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class GeneralUtility
{

    /**
     * Parses a Yaml File
     *
     * @param string $filePath
     * @return array
     * @static
     */
    static public function parseYamlFile($filePath)
    {
        return self::parseYaml(file_get_contents($filePath));
    }

    /**
     * Parse Yaml
     *
     * @param $contents
     * @return array
     * @throws Exception
     * @static
     */
    static public function parseYaml($contents)
    {
        try {
            $yaml = Yaml::parse($contents);
            return (is_array($yaml) ? $yaml : []);
        } catch (\Exception $exception) {
            throw new Exception('Could not parse Yaml file "' . $filePath . '" because:' . PHP_EOL
                . $exception->getMessage());
        }
    }

    /**
     * Gets the Home Directory
     * Returns the absolute path to the user's home directory
     *
     * @return string
     * @static
     */
    static public function getHomeDirectory()
    {
        $home = getenv('HOME');
        if (empty($home)) {
            // Windows compatibility.
            if ($userProfile = getenv('USERPROFILE')) {
                $home = $userProfile;
            } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
                $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            }
        }
        return $home;
    }

    /**
     * Generates a Random String
     *
     * @param int $length
     * @return string
     * @static
     */
    static public function generateRandomString($length = 10)
    {
        $characters = '0123456789bcdfghjklmnpqrstvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}