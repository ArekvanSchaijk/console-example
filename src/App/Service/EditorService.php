<?php
namespace AlterNET\Cli\App\Service;

use AlterNET\Cli\App;
use AlterNET\Cli\Utility\StringUtility;
use AlterNET\Package\Environment;

/**
 * Class EditorService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class EditorService implements AppServiceInterface
{

    /**
     * @var App
     */
    protected $app;

    /**
     * EditorService constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Opens the editor
     *
     * @param string|null $filePath
     * @return bool
     */
    public function open($filePath = null)
    {
        if (!StringUtility::isAbsolutePath($filePath)) {
            $filePath = $this->app->getWorkingDirectory() . '/' . $filePath;
        }
        // PhpStorm support
        if ($this->openPhpStorm($filePath)) {
            return true;
        }
        return false;
    }

    /**
     * Opens Php Storm
     *
     * @param string|null $filePath
     * @return bool
     */
    protected function openPhpStorm($filePath = null)
    {
        $executables = [
            '/usr/local/bin/pstorm',
            '/usr/bin/pstorm'
        ];
        // Windows
        if (Environment::isWindowsOs()) {
            $executables = [
                'C:\\Program Files (x86)\\JetBrains\\PhpStorm 2016.1\\bin\\PhpStorm.exe'
            ];
        }
        foreach ($executables as $executable) {
            if (file_exists($executable)) {
                if (!$filePath) {
                    $filePath = '.';
                }
                $this->app->process($executable . ' ' . $filePath);
                return true;
            }
        }
        return false;
    }

}