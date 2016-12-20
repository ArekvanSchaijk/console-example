<?php
namespace AlterNET\Cli\App\Service;

use AlterNET\Cli\App;

/**
 * Class GitService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class GitService implements AppServiceInterface
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
     * Clone Url
     *
     * @param $url
     * @return void
     */
    public function cloneUrl($url)
    {
        $this->app->process('git clone ' . $url . ' .');
    }

    /**
     * Gets the Remote Branches
     *
     * @return array
     */
    public function getRemoteBranches()
    {
        $branches = [];
        $lines = explode(PHP_EOL, $this->app->process('git branch -r'));
        foreach ($lines as $line) {
            $branch = trim($line);
            if (!empty($branch) && $branch !== 'origin/HEAD') {
                $branches[] = ltrim($branch, 'origin/');
            }
        }
        return $branches;
    }

    /**
     * Has Remote Branch
     *
     * @param $branchName
     * @return bool
     */
    public function hasRemoteBranch($branchName)
    {
        return in_array($branchName, $this->getRemoteBranches());
    }

    /**
     * Checkout
     *
     * @param string $branchName
     * @param bool $origin
     * @return void
     */
    public function checkout($branchName, $origin = TRUE)
    {
        $this->app->process('git checkout ' . ($origin ? 'origin/' : null) . $branchName);
    }

}