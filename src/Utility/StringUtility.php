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
     * @param int|bool $length
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

    /**
     * Gets the First Character
     *
     * @param string $string
     * @return string
     * @static
     */
    static public function getFirstCharacter($string)
    {
        return substr($string, 0, 1);
    }

    /**
     * Gets the Last Character of a string
     *
     * @param string $string
     * @return string
     * @static
     */
    static public function getLastCharacter($string)
    {
        return substr($string, -1);
    }

}