<?php
namespace AlterNET\Cli\App\Config\Environment;

use AlterNET\Cli\App\Config;
use AlterNET\Cli\Exception;
use AlterNET\Package\Environment;

/**
 * Class EnvironmentSelector
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class EnvironmentSelector
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
     * @var Config
     */
    protected $config;

    /**
     * EnvironmentSelector constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

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
        throw new Exception('Current environment is not configured.');
    }

    /**
     * All
     *
     * @return EnvironmentConfig[]
     */
    public function all()
    {
        $environments = [];
        if ($this->isLocal()) {
            $environments[] = $this->local();
        }
        if ($this->isDevelopment()) {
            $environments[] = $this->development();
        }
        if ($this->isTesting()) {
            $environments[] = $this->testing();
        }
        if ($this->isAcceptance()) {
            $environments[] = $this->acceptance();
        }
        if ($this->isProduction()) {
            $environments[] = $this->production();
        }
        return $environments;
    }

    /**
     * Is Local
     *
     * @return bool
     */
    public function isLocal()
    {
        return isset($this->config->getArray()['local']);
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
                $this->config,
                'Local',
                $this->config->getArray()['local'],
                $this->getDefaultEnvironmentConfig()
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
        return isset($this->config->getArray()['development']);
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
                $this->config,
                Environment::ENVIRONMENT_NAME_DEVELOPMENT,
                $this->config->getArray()['development'],
                $this->getDefaultEnvironmentConfig()
            );
        }
        return $this->development;
    }

    /**
     * Is Testing
     *
     * @return bool
     */
    public function isTesting()
    {
        return isset($this->config->getArray()['testing']);
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
                $this->config,
                Environment::ENVIRONMENT_NAME_TESTING,
                $this->config->getArray()['testing'],
                $this->getDefaultEnvironmentConfig()
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
        return isset($this->config->getArray()['acceptance']);
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
                $this->config,
                Environment::ENVIRONMENT_NAME_ACCEPTANCE,
                $this->config->getArray()['acceptance'],
                $this->getDefaultEnvironmentConfig()
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
        return isset($this->config->getArray()['production']);
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
                $this->config,
                Environment::ENVIRONMENT_NAME_PRODUCTION,
                $this->config->getArray()['production'],
                $this->getDefaultEnvironmentConfig()
            );
        }
        return $this->production;
    }

    /**
     * Gets the Default Environment Config Options
     *
     * @return array
     */
    protected function getDefaultEnvironmentConfig()
    {
        if (isset($this->config->getArray()['application']['environment'])) {
            return $this->config->getArray()['application']['environment'];
        }
        return [];
    }

}