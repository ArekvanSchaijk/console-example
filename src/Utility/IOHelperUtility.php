<?php
namespace AlterNET\Cli\Utility;

/**
 * Class IOHelperUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class IOHelperUtility
{

    /**
     * Gets the Bitbucket Project Headers
     *
     * @return array
     * @static
     */
    static public function getBitbucketProjectHeaders()
    {
        return [
            '#', 'Key', 'Name', 'Type', 'Public', 'Link'
        ];
    }

}