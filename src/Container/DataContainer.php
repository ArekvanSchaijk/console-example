<?php
namespace AlterNET\Cli\Container;

/**
 * Class DataContainer
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class DataContainer
{

    /**
     * @var DataContainer
     */
    static protected $container;

    /**
     * @var int
     */
    protected $selfUpdateTimestamp;

    /**
     * @var int
     */
    protected $templatesTimestamp;

    /**
     * Create
     *
     * @return DataContainer
     * @static
     */
    static public function create()
    {
        if (is_null(self::$container)) {
            if (file_exists(self::getDataFilePath())) {
                try {
                    if (($contents = file_get_contents(self::getDataFilePath()))) {
                        if (($data = unserialize($contents)) instanceof DataContainer) {
                            self::$container = $data;
                        }
                    }
                } catch (\Exception $exception) {
                }
            }
            if (!self::$container) {
                self::$container = new DataContainer();
            }
        }
        return self::$container;
    }

    /**
     * Gets the Data File Path
     *
     * @return string
     * @static
     */
    static public function getDataFilePath()
    {
        return CLI_HOME_PRIVATE . '/data';
    }

    /**
     * Gets the Self Update Timestamp
     *
     * @return int
     */
    public function getSelfUpdateTimestamp()
    {
        return $this->selfUpdateTimestamp;
    }

    /**
     * Sets the Self Update Timestamp
     *
     * @param int $selfUpdateTimestamp
     */
    public function setSelfUpdateTimestamp($selfUpdateTimestamp)
    {
        $this->selfUpdateTimestamp = $selfUpdateTimestamp;
    }

    /**
     * Gets the Templates Timestamp
     *
     * @return int
     */
    public function getTemplatesTimestamp()
    {
        if ($this->templatesTimestamp > time()) {
            return null;
        }
        return $this->templatesTimestamp;
    }

    /**
     * Sets the Templates Timestamp
     *
     * @param int $templatesTimestamp
     * @return void
     */
    public function setTemplatesTimestamp($templatesTimestamp)
    {
        $this->templatesTimestamp = $templatesTimestamp;
    }

    /**
     * Writes the container
     *
     * @return void
     */
    public function write()
    {
        file_put_contents(self::getDataFilePath(), serialize($this));
    }

}