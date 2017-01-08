<?php
namespace AlterNET\Cli\Local\Service;

use AlterNET\Cli\Utility\ConsoleUtility;
use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Herrera\Version\Parser;

/**
 * Class SelfService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class SelfService
{

    /**
     * @var SelfService
     */
    static protected $selfService;

    /**
     * @var bool
     */
    static protected $lockMajor = false;

    /**
     * @var bool
     */
    static protected $preRelease = true;

    /**
     * @var Manifest
     */
    protected $manifest;

    /**
     * @var Manager
     */
    protected $updateManager;

    /**
     * Creates the self service
     *
     * @return SelfService
     * @static
     */
    static public function create()
    {
        if (is_null(self::$selfService)) {
            self::$selfService = new SelfService();
        }
        return self::$selfService;
    }

    /**
     * Gets the Current Version
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return ConsoleUtility::getConfig()->getApplicationVersion();
    }

    /**
     * Is Development Master
     *
     * @return bool
     */
    public function isDevelopmentMaster()
    {
        if ($this->getCurrentVersion() === 'dev-master') {
            return true;
        }
        return false;
    }

    /**
     * Gets the Manifest
     *
     * @return Manifest
     */
    public function getManifest()
    {
        if (!$this->manifest) {
            $this->manifest = Manifest::loadFile(
                ConsoleUtility::getConfig()->self()->getManifestUrl()
            );
        }
        return $this->manifest;
    }

    /**
     * Gets the Update Manager
     *
     * @return Manager
     */
    public function getUpdateManager()
    {
        if (!$this->updateManager) {
            $this->updateManager = new Manager($this->getManifest());
        }
        return $this->updateManager;
    }

    /**
     * Gets the Newest Version
     *
     * @return string|false
     */
    public function getNewestVersion()
    {
        if (!$this->isDevelopmentMaster()) {
            $update = $this->getManifest()->findRecent(Parser::toVersion($this->getCurrentVersion()),
                self::$lockMajor, self::$preRelease);
            if ($update) {
                return (string)$update->getVersion();
            }
        }
        return false;
    }

    /**
     * Is New Version
     *
     * @return bool
     */
    public function isNewVersion()
    {
        return (bool)$this->getNewestVersion();
    }

    /**
     * Update
     *
     * @return string|bool
     */
    public function update()
    {
        $version = $this->getNewestVersion();
        if ($version) {
            if ($this->getUpdateManager()->update($this->getCurrentVersion(), self::$lockMajor, self::$preRelease)) {
                $this->storeTimestamp();
                return $version;
            }
        }
        return false;
    }

    /**
     * Gets the Timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return ConsoleUtility::getDataContainer()->getSelfUpdateTimestamp();
    }

    /**
     * Stores the Timestamp
     *
     * @return void
     */
    public function storeTimestamp()
    {
        ConsoleUtility::getDataContainer()->setSelfUpdateTimestamp(time());
        ConsoleUtility::getDataContainer()->write();
    }

}