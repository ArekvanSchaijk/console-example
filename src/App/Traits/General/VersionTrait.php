<?php
namespace AlterNET\Cli\App\Traits\General;

/**
 * Class VersionTrait
 * @author Arek van Schaijk <arek@alternet.nl>
 */
trait VersionTrait
{

    /**
     * @var string
     */
    protected $version;

    /**
     * Gets the Version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the Version
     *
     * @param string $version
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

}