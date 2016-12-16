<?php
namespace AlterNET\Cli\Utility;

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
        return Yaml::parse(file_get_contents($filePath));
    }

}