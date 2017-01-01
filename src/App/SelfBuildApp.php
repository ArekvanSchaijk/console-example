<?php
namespace AlterNET\Cli\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Driver\BitbucketDriver;
use AlterNET\Cli\Utility\ConsoleUtility;
use AlterNET\Cli\Utility\GeneralUtility;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SelfBuildApp
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class SelfBuildApp extends App
{

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $revision;

    /**
     * SelfApp constructor.
     */
    public function __construct()
    {
        parent::__construct(
            ConsoleUtility::createBuildWorkingDirectory('selfapp_')
        );
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize()
    {
        $this->getGitService()->cloneUrl(
            $this->cliConfig->self()->getRemoteUrl()
        );
        // Sets the version
        $this->version = $this->getGitService()->getHighestTag();
        // Sets the revision
        $this->revision = $this->getGitService()->getHighestTagRevision();
    }

    /**
     * Gets the Version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Gets the Revision
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Writes the Version Into the Config File
     *
     * @return void
     */
    protected function writeVersionIntoConfigFile()
    {
        $configFilePath = $this->getWorkingDirectory() . '/config.yaml';
        $yaml = GeneralUtility::parseYamlFile($configFilePath);
        $yaml['application']['version'] = $this->getVersion();
        file_put_contents($configFilePath, Yaml::dump($yaml));
    }

    /**
     * Gets the Manifest Array
     *
     * @return array
     */
    public function getManifestArray()
    {
        $manifest = [];
        foreach (scandir($this->getDownloadWorkingDirectory()) as $fileName) {
            if (strpos($fileName, '.phar') !== false && strpos($fileName, '.pubkey') === false) {
                $manifest[] = [
                    'name' => 'alternet.phar',
                    'sha1' => sha1_file($this->getDownloadWorkingDirectory() . '/' . $fileName),
                    'url' => $this->cliConfig->self()->getDownloadUrl() . $fileName,
                    'version' => rtrim(ltrim($fileName, 'alternet-'), '.phar')
                ];
            }
        }
        return $manifest;
    }

    /**
     * Gets the Download Working Directory
     *
     * @return string
     */
    public function getDownloadWorkingDirectory()
    {
        return $this->getWebWorkingDirectory() . '/downloads';
    }

    /**
     * Gets the Version File Name
     *
     * @return string
     */
    public function getVersionFileName()
    {
        return 'alternet-' . $this->getVersion() . '.phar';
    }

    /**
     * Gets the New Version File Path
     *
     * @return string
     */
    public function getNewVersionFilePath()
    {
        return $this->getDownloadWorkingDirectory() . '/' . $this->getVersionFileName();
    }

    /**
     * Gets the Manifest File Path
     *
     * @return string
     */
    public function getManifestFilePath()
    {
        return $this->getWebWorkingDirectory() . '/manifest.json';
    }

    /**
     * Build
     *
     * @return void
     */
    public function build()
    {
        $this->getGitService()->checkout($this->getRevision(), false);
        $this->writeVersionIntoConfigFile();
        $this->getComposerService()->install();
        $this->process('box build');
        $this->getGitService()->checkout('master');
        ConsoleUtility::fileSystem()->copy(
            $this->getWorkingDirectory() . '/alternet.phar',
            $this->getNewVersionFilePath()
        );

        ConsoleUtility::fileSystem()->copy(
            $this->getWorkingDirectory() . '/alternet.phar.pubkey',
            $this->getNewVersionFilePath() . '.pubkey'
        );

        file_put_contents(
            $this->getManifestFilePath(),
            json_encode($this->getManifestArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Release
     *
     * @param BitbucketDriver $bitbucketDriver
     * @return void
     */
    public function release(BitbucketDriver $bitbucketDriver)
    {
        // Gets the Bitbucket repository
        $repository = $bitbucketDriver->getRepositoryByRemoteUrl($this->getRemoteUrl());
        // Gets the master branch
        $master = $repository->getBranchByName('master');
        // Creates a new branch name on the server
        $branch = $repository->createBranch($master, 'CLI/ReleaseVersion-' . $this->getVersion());
        $this->process('git fetch;git checkout -b ' . $branch->getName());
        // Stages files
        $filesToStage = [
            $this->getNewVersionFilePath(),
            $this->getNewVersionFilePath() . '.pubkey',
            $this->getManifestFilePath()
        ];
        foreach ($filesToStage as $fileToStage) {
            $this->process('git add ' . str_replace($this->getWorkingDirectory() . '/', null, $fileToStage));
        }
        // Commits and pushes it
        $this->process('git commit -m \'[CLI] Release of version ' . $this->getVersion() . '\';git push -u origin '
            . $branch->getName());
        // Creates a pull request
        $pullRequest = $repository->createPullRequest(
            '[CLI] Release of version ' . $this->getVersion(),
            'Containing a new .phar file and update of the manifest.json file.',
            $branch,
            $master
        );
        // And finally merges it
        $pullRequest->merge();
        // And lets remove the remote branch now
        $this->process('git push origin --delete ' . $branch->getName());
    }

    /**
     * SelfApp destructor.
     */
    public function __destruct()
    {
        $this->remove();
    }

}