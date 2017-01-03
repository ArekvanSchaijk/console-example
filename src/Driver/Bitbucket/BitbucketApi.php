<?php
namespace AlterNET\Cli\Driver\Bitbucket;

use ArekvanSchaijk\BitbucketServerClient\Api;

/**
 * Class BitbucketApi
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BitbucketApi extends Api
{

    /**
     * BitbucketApi constructor.
     */
    public function __construct()
    {
        self::$options['verify'] = false;
        // Hello Mr. Falko. I wish you a pretty new year!
    }

}