<?php
namespace AlterNET\Cli\Config;

/**
 * Class AbstractConfig
 * @author Arek van Schaijk <arek@alternet.nl>
 */
abstract class AbstractConfig
{

    /**
     * @var array
     */
    protected $config;

    /**
     * AbstractConfig constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

}