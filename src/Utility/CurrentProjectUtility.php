<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\Config;

/**
 * Class CurrentProjectUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class CurrentProjectUtility
{

    /**
     * Is Cwd Inside a Project
     * Checks if the CWD (Current Working Directory) is inside a project
     *
     * @param int $maxSearchDepth
     * @return bool
     * @static
     */
    static public function isCwdInProject($maxSearchDepth = 20)
    {
        return (bool)self::getConfigFilePath($maxSearchDepth);
    }

    /**
     * Gets the Config File Path
     * Returns current project's config file path
     *
     * @param int $maxSearchDepth
     * @return string|bool
     * @static
     */
    static public function getConfigFilePath($maxSearchDepth = 20)
    {
        $config = Config::create();
        $directory = getcwd();
        $configFilePath = $config->projects()->getProjectConfigFilePath();
        for ($i = 0; $i < $maxSearchDepth; $i++) {
            if ($i > 0) {
                $directory .= '/..';
            }
            if (file_exists($directory . '/' . $configFilePath)) {
                return realpath($directory . '/' . $configFilePath);
            }
        }
        return FALSE;
    }

}