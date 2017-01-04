<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\Exception;

/**
 * Class TemplateUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class TemplateUtility
{

    const

        TYPE_APPLICATION = 'application',
        TYPE_ENVIRONMENT = 'environment';

    /**
     * Gets the Template File Path
     *
     * @param string $name
     * @param string $type
     * @param bool $parseVariables
     * @param array $inputArray
     * @return array
     * @throws Exception
     * @static
     */
    static public function get($name, $type, $parseVariables = false, array $inputArray = null)
    {
        $path = CLI_ROOT . '/templates/' . $type . '/' . $name . '.yaml';
        if (!file_exists($path)) {
            throw new Exception(ucfirst($type) . ' template with name "' . $name . '" does not exists.');
        }
        if ($parseVariables && $inputArray) {
            $contents = file_get_contents($path);
            $contents = StringUtility::parseConfigVariables($contents, $inputArray);
            return GeneralUtility::parseYaml($contents);
        }
        return (GeneralUtility::parseYamlFile($path) ?: []);
    }

}