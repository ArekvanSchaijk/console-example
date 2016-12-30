<?php
namespace AlterNET\Cli\Driver;

use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Driver\Bitbucket\BitbucketApi as Api;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Project;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;
use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository\Branch;

/**
 * Class BitbucketDriver
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketDriver
{

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var Config
     */
    protected $config;

    /**
     * BitbucketDriver constructor.
     * @param CrowdContainer $crowdContainer
     */
    public function __construct(CrowdContainer $crowdContainer)
    {
        $this->config = ConsoleUtility::getConfig();
        $this->initialize($crowdContainer);
    }

    /**
     * Initialize
     *
     * @param CrowdContainer $crowdContainer
     * @return void
     */
    public function initialize(CrowdContainer $crowdContainer)
    {
        $this->api = new Api();
        // Sets the Bitbucket endpoint
        $this->api->setEndpoint(
            $this->config->bitbucket()->getEndpoint()
        );
        // Logs in with the Crowd credentials
        $this->api->login(
            $crowdContainer->username,
            $crowdContainer->password
        );
    }

    /**
     * Gets the Api
     *
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Gets all Repositories belonging to an App
     *
     * @return Repository[]
     */
    public function getAppRepositories()
    {
        $repositories = [];
        // Gets the repository selectors
        $selectors = $this->config->app()->getBitbucketRepositorySelectors();
        /* @var Project $project */
        foreach ($this->api->getProjects() as $project) {
            /* @var Repository $repository */
            foreach ($project->getRepositories() as $repository) {
                foreach ($selectors as $selector) {
                    if (substr($repository->getName(), 0, strlen($selector)) === $selector) {
                        $repositories[$repository->getProject()->getKey() . '/' . $repository->getName()] = $repository;
                    }
                }
            }
        }
        return $repositories;
    }

    /**
     * Gets the Repository By its Remote Url
     * This gets the repository by its (ssh clone url) remote url
     *
     * @param string $remoteUrl
     * @return Repository|null
     */
    public function getRepositoryByRemoteUrl($remoteUrl)
    {
        // Match on the project key
        preg_match('/7999\/(.*?)\//', $remoteUrl, $matches);
        if (isset($matches[1])) {
            /* @var Repository $repository */
            foreach ($this->getApi()->getRepositoriesByProject(trim(strtoupper($matches[1]))) as $repository) {
                if ($repository->getSshCloneUrl() === $remoteUrl) {
                    return $repository;
                }
            }
        }
        return null;
    }

}