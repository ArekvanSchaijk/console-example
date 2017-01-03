<?php
namespace AlterNET\Cli\App\Traits\General;

/**
 * Class VersionTrait
 * @author Arek van Schaijk <arek@alternet.nl>
 */
trait RevisionTrait
{

    /**
     * @var string
     */
    protected $revision;

    /**
     * Gets the Revision
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Sets the Revision
     *
     * @param string $revision
     * @return void
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

}