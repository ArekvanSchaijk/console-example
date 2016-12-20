<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\App;
use AlterNET\Package\Environment;

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

    /**
     * Is Cwd In App
     * Checks if the Current Working Directory is in the directory of an App
     *
     * @return bool
     * @static
     */
    static public function isCwdInApp()
    {
        $config = ConsoleUtility::getConfig();
        $workingDirectory = getcwd();
        $configFilePath = $config->app()->getRelativeConfigFilePath();
        for ($i = 0; $i < $config->app()->getConfigMaxSearchDepth(); $i++) {
            if ($i > 0) {
                $workingDirectory .= '/..';
            }
            if (file_exists($workingDirectory . '/' . $configFilePath)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Creates a New App
     *
     * @param string|null $gitCloneUrl
     * @return App
     * @static
     */
    static public function createNewApp($gitCloneUrl = null)
    {
        $config = ConsoleUtility::getConfig();
        $workingDirectory = $config->getCliWorkingDirectory()
            . '/builds/app_' . GeneralUtility::generateRandomString(20);
        ConsoleUtility::fileSystem()->mkdir($workingDirectory);
        $newApp = new App($workingDirectory);
        if ($gitCloneUrl) {
            $newApp->getGitService()->cloneUrl($gitCloneUrl);
        }
        return $newApp;
    }

    /**
     * Gets the Default Environment Branch Names
     *
     * @return array
     * @static
     */
    static public function getDefaultEnvironmentBranchNames()
    {
        $config = ConsoleUtility::getConfig();
        return [
            Environment::ENVIRONMENT_NAME_DEVELOPMENT => $config->bitbucket()->getDefaultDevelopmentBranch(),
            Environment::ENVIRONMENT_NAME_TESTING => $config->bitbucket()->getDefaultTestingBranch(),
            Environment::ENVIRONMENT_NAME_ACCEPTANCE => $config->bitbucket()->getDefaultAcceptanceBranch(),
            Environment::ENVIRONMENT_NAME_PRODUCTION => $config->bitbucket()->getDefaultProductionBranch()
        ];
    }

    /**
     * Gets the Current Environment Branch Name
     * Gets the branch name belonging to the current environment
     *
     * @return string|string
     * @static
     */
    static public function getCurrentEnvironmentBranchName()
    {
        $config = ConsoleUtility::getConfig();
        if (Environment::isProductionEnvironment()) {
            return $config->bitbucket()->getDefaultProductionBranch();
        } elseif (Environment::isAcceptanceEnvironment()) {
            return $config->bitbucket()->getDefaultAcceptanceBranch();
        } elseif (Environment::isTestingEnvironment()) {
            return $config->bitbucket()->getDefaultTestingBranch();
        } elseif (Environment::isDevelopmentEnvironment()) {
            return $config->bitbucket()->getDefaultDevelopmentBranch();
        }
        return false;
    }

    /**
     * Is Environment BranchName
     *
     * @param string $branchName
     * @return bool
     * @static
     */
    static public function isEnvironmentBranchName($branchName)
    {
        return in_array($branchName, self::getDefaultEnvironmentBranchNames());
    }

}