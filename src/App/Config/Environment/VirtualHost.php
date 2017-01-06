<?php
namespace AlterNET\Cli\App\Config\Environment;

/**
 * Class VirtualHost
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class VirtualHost
{

    /**
     * @var EnvironmentConfig
     */
    protected $environmentConfig;

    /**
     * @var array
     */
    protected $default = [];

    /**
     * @var array
     */
    protected $extraHttp = [];

    /**
     * @var array
     */
    protected $extraSsl = [];

    /**
     * VirtualHost constructor.
     * @param EnvironmentConfig $environmentConfig
     */
    public function __construct(EnvironmentConfig $environmentConfig)
    {
        $this->environmentConfig = $environmentConfig;
    }

    /**
     * Gets the Environment Config
     *
     * @return EnvironmentConfig
     */
    public function getEnvironmentConfig()
    {
        return $this->environmentConfig;
    }

    /**
     * Get Forward To Https Rows
     *
     * @return array
     */
    static public function getForwardToHttpsRows()
    {
        return [
            'RewriteEngine On',
            'RewriteCond %{HTTPS} off',
            'RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI}'
        ];
    }

    /**
     * Gets the Http Rows
     *
     * @return array
     */
    public function getHttpRows()
    {
        $rows = array_merge($this->getDefault(), $this->getExtraHttp());
        // Adds the forward to https rows
        if ($this->getEnvironmentConfig()->isSsl() && $this->getEnvironmentConfig()->isForceHttps()) {
            $rows = array_merge(self::getForwardToHttpsRows(), $rows);
        }
        return $rows;
    }

    /**
     * Gets the Ssl Rows
     *
     * @return array
     */
    public function getSslRows()
    {
        return array_merge($this->getDefault(), $this->getExtraSsl());
    }

    /**
     * Gets the Default
     *
     * @return array
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Sets the Default
     *
     * @param array $default
     * @return void
     */
    public function setDefault(array $default)
    {
        $this->default = $default;
    }

    /**
     * Add Defaults
     *
     * @param array|string $default
     * @return void
     */
    public function addDefault($default)
    {
        if (is_array($default)) {
            $this->default = array_merge($this->default, $default);
        } else {
            $this->default[] = $default;
        }
    }

    /**
     * Gets the ExtraHttp
     *
     * @return array
     */
    public function getExtraHttp()
    {
        return $this->extraHttp;
    }

    /**
     * Sets the ExtraHttp
     *
     * @param array $extraHttp
     * @return void
     */
    public function setExtraHttp(array $extraHttp)
    {
        $this->extraHttp = $extraHttp;
    }

    /**
     * Adds Extra Http
     *
     * @param array|string $extraHttp
     * @return void
     */
    public function addExtraHttp($extraHttp)
    {
        if (is_array($extraHttp)) {
            $this->extraHttp = array_merge($this->extraHttp, $extraHttp);
        } else {
            $this->extraHttp[] = $extraHttp;
        }
    }

    /**
     * Gets the ExtraSsl
     *
     * @return array
     */
    public function getExtraSsl()
    {
        return $this->extraSsl;
    }

    /**
     * Sets the ExtraSsl
     *
     * @param array $extraSsl
     * @return void
     */
    public function setExtraSsl(array $extraSsl)
    {
        $this->extraSsl = $extraSsl;
    }

    /**
     * Add Extra Ssl
     *
     * @param array|string $extraSsl
     * @return void
     */
    public function addExtraSsl($extraSsl)
    {
        if (is_array($extraSsl)) {
            $this->extraSsl = array_merge($this->extraSsl, $extraSsl);
        } else {
            $this->extraSsl[] = $extraSsl;
        }
    }

}