<?php
namespace AlterNET\Cli\App\Config\Environment;

use AlterNET\Cli\Config\AbstractConfig;
use AlterNET\Cli\Exception;
use AlterNET\Package\Environment;

/**
 * Class EnvironmentSelector
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class EnvironmentSelector extends AbstractConfig
{

    /**
     * @var EnvironmentConfig
     */
    protected $local;

    /**
     * @var EnvironmentConfig
     */
    protected $development;

    /**
     * @var EnvironmentConfig
     */
    protected $testing;

    /**
     * @var EnvironmentConfig
     */
    protected $acceptance;

    /**
     * @var EnvironmentConfig
     */
    protected $production;

    /**
     * Current
     *
     * @return EnvironmentConfig
     * @throws Exception
     */
    public function current()
    {
        if (Environment::isProductionEnvironment()) {
            return $this->production();
        } elseif (Environment::isAcceptanceEnvironment()) {
            return $this->acceptance();
        } elseif (Environment::isTestingEnvironment()) {
            return $this->testing();
        } elseif (Environment::isRemoteDevelopmentEnvironment()) {
            return $this->development();
        } elseif (Environment::isLocalEnvironment()) {
            return $this->local();
        }
        throw new Exception('No current environment found.');
    }

    /**
     * Local
     *
     * @return EnvironmentConfig
     * @throws Exception
     */
    public function local()
    {
        if (is_null($this->local)) {
            if (!isset($this->config['Local'])) {
                throw new Exception('No local environment configuration found.');
            }
            $this->local = new EnvironmentConfig(
                'Local',
                $this->config['Local']
            );
        }
        return $this->local;
    }

    /**
     * Development
     *
     * @return EnvironmentConfig
     * @throws Exception
     */
    public function development()
    {
        if (is_null($this->development)) {
            if (!isset($this->config['Development'])) {
                throw new Exception('No development environment configuration found.');
            }
            $this->development = new EnvironmentConfig(
                Environment::ENVIRONMENT_NAME_DEVELOPMENT,
                $this->config['Development']
            );
        }
        return $this->development;
    }

    /**
     * Testing
     *
     * @return EnvironmentConfig
     * @throws Exception
     */
    public function testing()
    {
        if (is_null($this->testing)) {
            if (!isset($this->config['Testing'])) {
                throw new Exception('No testing environment configuration found.');
            }
            $this->testing = new EnvironmentConfig(
                Environment::ENVIRONMENT_NAME_TESTING,
                $this->config['Testing']
            );
        }
        return $this->testing;
    }

    /**
     * Acceptance
     *
     * @return EnvironmentConfig
     * @throws Exception
     */
    public function acceptance()
    {
        if (is_null($this->acceptance)) {
            if (!isset($this->config['Acceptance'])) {
                throw new Exception('No testing environment configuration found.');
            }
            $this->acceptance = new EnvironmentConfig(
                Environment::ENVIRONMENT_NAME_ACCEPTANCE,
                $this->config['Acceptance']
            );
        }
        return $this->acceptance;
    }

    /**
     * Production
     *
     * @return EnvironmentConfig
     * @throws Exception
     */
    public function production()
    {
        if (is_null($this->production)) {
            if (!isset($this->config['Production'])) {
                throw new Exception('No testing environment configuration found.');
            }
            $this->production = new EnvironmentConfig(
                Environment::ENVIRONMENT_NAME_PRODUCTION,
                $this->config['Production']
            );
        }
        return $this->production;
    }

}