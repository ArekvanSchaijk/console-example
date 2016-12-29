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
     * Is Local
     *
     * @return bool
     */
    public function isLocal()
    {
        return isset($this->config['Local']);
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
            if (!$this->isLocal()) {
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
     * Is Development
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return isset($this->config['Development']);
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
            if (!$this->isDevelopment()) {
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
     * Is Testing
     *
     * @return bool
     */
    public function isTesting() {
        return isset($this->config['Testing']);
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
            if (!$this->isTesting()) {
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
     * Is Acceptance
     *
     * @return bool
     */
    public function isAcceptance()
    {
        return isset($this->config['Acceptance']);
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
            if (!$this->isAcceptance()) {
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
     * Is Production
     *
     * @return bool
     */
    public function isProduction()
    {
        return isset($this->config['Production']);
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
            if (!$this->isProduction()) {
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