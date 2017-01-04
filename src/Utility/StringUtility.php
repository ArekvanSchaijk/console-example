<?php
namespace AlterNET\Cli\Utility;

use AlterNET\Cli\App\Exception;

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
     * Is Absolute Path
     *
     * @param string $path
     * @return bool
     * @static
     */
    static public function isAbsolutePath($path)
    {
        if (StringUtility::getFirstCharacter($path) === '/' || StringUtility::getFirstCharacter($path) === '\\') {
            return true;
        }
        return false;
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

    /**
     * Parses the Configuration Variables
     *
     * @param string $string
     * @param array $inputArray
     * @return string
     * @throws Exception
     * @static
     */
    static public function parseConfigVariables($string, array $inputArray)
    {
        if (strpos($string, '{') === false) {
            return $string;
        }
        preg_match_all('/{(.*?)}/', $string, $matches);
        if ($matches[1]) {
            $variables = array_unique($matches[1]);
            foreach ($variables as $variable) {
                $name = '{' . $variable . '}';
                $namePhrased = '"' . $name . '"';
                if (strpos($variable, '.') !== false) {
                    $i = 0;
                    $chain = null;
                    foreach (explode('.', $variable) as $piece) {
                        switch ($i) {
                            case 0:
                                if (isset($inputArray[$piece])) {
                                    $chain = $inputArray[$piece];
                                    break;
                                }
                                throw new Exception('Could not parse variable ' . $namePhrased . ' because' .
                                    ' section "' . $piece . '" is not supported.');
                            default:
                                if (is_object($chain)) {
                                    $methods = [
                                        'get' . ucfirst($piece),
                                        $piece
                                    ];
                                    $gotMethod = false;
                                    foreach ($methods as $method) {
                                        $gotMethod = true;
                                        if (method_exists($chain, $method)) {
                                            $chain = $chain->$method();
                                        }
                                        break;
                                    }
                                    if (!$gotMethod) {
                                        throw new Exception('Could not parse variable ' . $namePhrased . ' because' .
                                            ' "' . $piece . '" is not a method.');
                                    }
                                } elseif (is_array($chain)) {
                                    if (isset($chain[$piece])) {
                                        $chain = $chain[$piece];
                                    }
                                }
                        }
                        $i++;
                    }
                    if (!is_object($chain) && !is_array($chain)) {
                        $value = $chain;
                        if (is_bool($chain)) {
                            if ($chain === true) {
                                $value = 'true';
                            } else {
                                $value = 'false';
                            }
                        }
                        $string = str_replace($name, $value, $string);
                    } else {
                        throw new Exception('Could not parse variable ' . $namePhrased);
                    }
                }
            }
        }
        return $string;
    }

}