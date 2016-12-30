<?php
namespace AlterNET\Cli\App\Service;

use AlterNET\Cli\App;

/**
 * Class ComposerService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ComposerService implements AppServiceInterface
{

    /**
     * @var App
     */
    protected $app;

    /**
     * Git constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Install
     *
     * @return void
     */
    public function install()
    {
        $this->app->process('composer install');
    }

    /**
     * Update
     *
     * @return void
     */
    public function update()
    {
        $this->app->process('composer update');
    }

}