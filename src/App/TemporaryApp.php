<?php
namespace AlterNET\Cli\App;

use AlterNET\Cli\App;

/**
 * Class TemporaryApp
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class TemporaryApp extends App
{

    /**
     * SelfApp destructor.
     */
    public function __destruct()
    {
        // Yeah. It destroys itself ;)
        $this->remove();
    }

}