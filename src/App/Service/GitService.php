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
     * Fetch
     *
     * @return void
     */
    public function fetch()
    {
        $this->app->process('git fetch');
    }

    /**
     * Add
     *
     * @param string|null $file
     * @return void
     */
    public function add($file = null)
    {
        $this->app->process('git add ' . ($file ?: '.'));
    }

    /**
     * Commit
     *
     * @param string $message
     * @return void
     */
    public function commit($message)
    {
        $this->app->process('git commit -m \'' . $message . '\'');
    }

    /**
     * Push
     *
     * @param string|null $branch
     * @return void
     */
    public function push($branch = null)
    {
        $this->app->process('git push -u origin ' . ($branch ? : 'HEAD'));
    }

    /**
     * Clones an Url
     *
     * @param string $url
     * @return void
     */
    public function cloneUrl($url)
    {
        $this->app->process('git clone ' . $url . ' .');
    }

    /**
     * Deletes a Remote Branch
     *
     * @param string $branch
     * @return void
     */
    public function deleteRemoteBranch($branch)
    {
        $this->app->process('git push origin --delete ' . $branch);
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