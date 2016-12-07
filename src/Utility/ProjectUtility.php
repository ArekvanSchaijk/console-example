<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\AppConfig;
use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use AlterNET\Package\Environment;
use ArekvanSchaijk\BitbucketServerClient\Api;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository\Branch;

/**
 * Class ProjectUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ProjectUtility
{

    /**
     * Gets the Config
     *
     * @param string $projectRootPath
     * @return AppConfig|bool
     * @static
     */
    static public function getConfig($projectRootPath)
    {
        if (!isset($GLOBALS['ALTERNET_CLI_APP_CONF_VARS'][md5($projectRootPath)])) {
            $config = Config::create();
            $configFilePath = $projectRootPath . '/' . $config->projects()->getProjectConfigFilePath();
            if (file_exists($configFilePath)) {
                $GLOBALS['ALTERNET_CLI_APP_CONF_VARS'][md5($projectRootPath)] = new AppConfig($configFilePath);
            } else {
                return FALSE;
            }
        }
        return $GLOBALS['ALTERNET_CLI_APP_CONF_VARS'][md5($projectRootPath)];
    }

    /**
     * Gets Repositories
     * Gets all Repositories belonging to an project
     *
     * @param CrowdContainer $crowdContainer
     * @return Repository[]
     */
    static public function getRepositories(CrowdContainer $crowdContainer)
    {
        $config = Config::create();
        $bitbucket = new Api();
        // Sets the Bitbucket endpoint
        $bitbucket->setEndpoint(
            $config->bitbucket()->getEndpoint()
        );
        // Logs in into Bitbucket with the Crowd Credentials
        $bitbucket->login(
            $crowdContainer->username,
            $crowdContainer->password
        );
        $projects = [];
        // Gets the repository selectors
        $selectors = $config->projects()->getBitbucketRepositorySelectors();
        /* @var Project $project */
        foreach ($bitbucket->getProjects() as $project) {
            /* @var Repository $repository */
            foreach ($project->getRepositories() as $repository) {
                foreach ($selectors as $selector) {
                    if (substr($repository->getName(), 0, strlen($selector)) === $selector) {
                        $projects[$project->getKey() . '/' . $repository->getName()] = $repository;
                    }
                }
            }
        }
        return $projects;
    }

    /**
     * Get Current Environment Branch
     * Gets the environment's related branch
     *
     * @param Repository $repository
     * @return Branch|bool
     * @static
     */
    static public function getCurrentEnvironmentBranch(Repository $repository)
    {
        $config = Config::create();
        $neededBranch = null;
        if (Environment::isDevelopmentEnvironment()) {
            $neededBranch = $config->bitbucket()->getDefaultDevelopmentBranch();
        } elseif (Environment::isTestingEnvironment()) {
            $neededBranch = $config->bitbucket()->getDefaultTestingBranch();
        } elseif (Environment::isAcceptanceEnvironment()) {
            $neededBranch = $config->bitbucket()->getDefaultAcceptanceBranch();
        }
        if ($neededBranch) {
            $branches = $repository->getBranches();
            /* @var Branch $branch */
            foreach ($branches as $branch) {
                if ($branch->getName() === $neededBranch) {
                    return $branch;
                }
            }
        }
        return FALSE;
    }

    /**
     * Gets the Default Environment Branch Names
     *
     * @return array
     * @static
     */
    static public function getDefaultEnvironmentBranchNames()
    {
        $config = Config::create();
        return [
            Environment::ENVIRONMENT_NAME_DEVELOPMENT => $config->bitbucket()->getDefaultDevelopmentBranch(),
            Environment::ENVIRONMENT_NAME_TESTING => $config->bitbucket()->getDefaultTestingBranch(),
            Environment::ENVIRONMENT_NAME_ACCEPTANCE => $config->bitbucket()->getDefaultAcceptanceBranch(),
            Environment::ENVIRONMENT_NAME_PRODUCTION => $config->bitbucket()->getDefaultProductionBranch()
        ];
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