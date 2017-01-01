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
     * Gets the Remote Url
     *
     * @return string
     */
    public function getRemoteUrl()
    {
        return trim($this->app->process('git config --get remote.origin.url'));
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
    public function checkout($branchName, $origin = true)
    {
        $this->app->process('git checkout ' . ($origin ? 'origin/' : null) . $branchName);
    }

    /**
     * Gets the Highest Tag
     *
     * @return string
     */
    public function getHighestTag()
    {
        return trim($this->app->process('git describe --abbrev=0 --tags'));
    }

    /**
     * Gets the Highest Tag
     *
     * @return string
     */
    public function getHighestTagRevision()
    {
        return trim($this->app->process('git rev-list --abbrev=0 --tags --max-count=1'));
    }

}