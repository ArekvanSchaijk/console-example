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
     * Writes the container
     *
     * @return void
     */
    public function write()
    {
        file_put_contents(self::getDataFilePath(), serialize($this));
    }

}