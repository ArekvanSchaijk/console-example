<?php
namespace AlterNET\Cli\App\Service;

use AlterNET\Cli\App;
use AlterNET\Cli\Config\LocalConfig;
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
     * Is Enabled
     * Checks if the editor management is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->app->getLocalConfig()->isOptionEditorManagement();
    }

    /**
     * Enable
     *
     * @return void
     */
    public function enable()
    {
        $this->app->getLocalConfig()->setOption(LocalConfig::OPTION_EDITOR_MANAGEMENT, true);
        $this->app->getLocalConfig()->write();
    }

    /**
     * Enable
     *
     * @return void
     */
    public function disable()
    {
        $this->app->getLocalConfig()->setOption(LocalConfig::OPTION_EDITOR_MANAGEMENT, false);
        $this->app->getLocalConfig()->write();
    }

    /**
     * Opens the editor
     *
     * @param string|null $filePath
     * @return bool
     */
    public function open($filePath = null)
    {
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
            $executables = [];
            $jetBrainsWorkingDirectory = 'C:\\Program Files (x86)\\JetBrains';
            if (file_exists($jetBrainsWorkingDirectory)) {
                foreach (scandir($jetBrainsWorkingDirectory, SCANDIR_SORT_DESCENDING) as $directory) {
                    // Scans for PhpStorm [year].[build]
                    if (strpos($directory, 'PhpStorm 201') !== false) {
                        $executables[] = $jetBrainsWorkingDirectory . '\\' . $directory . '\\bin\\PhpStorm.bat';
                    }
                }
            }
        }
        foreach ($executables as $executable) {
            if (file_exists($executable)) {
                if (Environment::isWindowsOs()) {
                    $this->app->process('PhpStorm.bat ' . $this->app->getWorkingDirectory(), dirname($executable));
                } else {
                    if (!$filePath) {
                        $filePath = '.';
                    }
                    $this->app->process($executable . ' ' . $filePath);
                }
                return true;
            }
        }
        return false;
    }

}