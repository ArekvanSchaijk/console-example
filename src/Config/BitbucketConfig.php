<?php
namespace AlterNET\Cli\Config;

/**
 * Class BitbucketConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketConfig extends AbstractConfig
{

    /**
     * Gets the Endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->config['endpoint'];
    }

    /**
     * Gets the Default Production Branch
     *
     * @return string
     */
    public function getDefaultProductionBranch()
    {
        return (string)$this->config['default_production_branch'];
    }

    /**
     * Gets the Default Acceptance Branch
     *
     * @return string
     */
    public function getDefaultAcceptanceBranch()
    {
        return (string)$this->config['default_acceptance_branch'];
    }

    /**
     * Gets the Default Testing Branch
     *
     * @return string
     */
    public function getDefaultTestingBranch()
    {
        return (string)$this->config['default_testing_branch'];
    }

    /**
     * Gets the Default Development Branch
     *
     * @return string
     */
    public function getDefaultDevelopmentBranch()
    {
        return (string)$this->config['default_development_branch'];
    }

}