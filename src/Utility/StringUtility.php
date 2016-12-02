<?php
namespace AlterNET\Cli\Utility;

/**
 * Class StringUtility
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class StringUtility
{

    /**
     * Crop
     *
     * @param string $string
     * @param int|bool$length
     * @param string|null $append
     * @return string
     * @static
     */
    static public function crop($string, $length, $append = '<comment>...</comment>')
    {
        if ($length && strlen($string) > $length) {
            return trim(substr($string, 0, $length)) . $append;
        }
        return $string;
    }

}