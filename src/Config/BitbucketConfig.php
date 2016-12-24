<?php
namespace AlterNET\Cli\Config;
use AlterNET\Cli\Utility\ConsoleUtility;

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

    /**
     * Gets the Project Options
     *
     * @param string $projectKey
     * @return array
     */
    public function getProjectCreateRepoOptions($projectKey)
    {
        $all = [];
        if (isset($this->config['projects']['all']['options']['create_repo'])
            && is_array($this->config['projects']['all']['options']['create_repo'])
        ) {
            $all = $this->config['projects']['all']['options']['create_repo'];
        }
        $projectOptions = [];
        if (isset($this->config['projects'][$projectKey]['options']['create_repo'])
            && is_array($this->config['projects'][$projectKey]['options']['create_repo'])
        ) {
            $projectOptions = $this->config['projects'][$projectKey]['options']['create_repo'];
        }
        return array_merge($all, $projectOptions);
    }

    /**
     * Gets the Project HipChat RoomId
     *
     * @param string $projectKey
     * @return bool|int
     */
    public function getProjectHipChatRoomId($projectKey)
    {
        $roomId = false;
        if (isset($this->config['projects'][$projectKey]['hipchat'])) {
            $roomId = (int)$this->config['projects'][$projectKey]['hipchat'];
            if (!$roomId) {
                $roomId = false;
            }
        }
        return $roomId;
    }

    /**
     * Gets the Project Composer Config
     *
     * @param $projectKey
     * @return array
     */
    public function getProjectComposerConfig($projectKey)
    {
        $composerConfig = ConsoleUtility::getConfig()->composer()->getArray();
        if (isset($this->config['projects'][$projectKey]['composer'])
            && is_array($this->config['projects'][$projectKey]['composer'])
        ) {
            return array_merge($composerConfig, $this->config['projects'][$projectKey]['composer']);
        }
        return $composerConfig;
    }

}