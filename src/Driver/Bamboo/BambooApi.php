<?php
namespace AlterNET\Cli\Driver\Bamboo;

use ArekvanSchaijk\BambooServerClient\Api;

/**
 * Class BambooApi
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BambooApi extends Api
{

    /**
     * BitbucketApi constructor.
     */
    public function __construct()
    {
        self::$options['verify'] = false;
    }

}