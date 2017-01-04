<?php
namespace AlterNET\Cli\App\Traits\Bitbucket;

use ArekvanSchaijk\BitbucketServerClient\Api\Entity\Repository;

/**
 * Class RepositoryTrait
 * @author Arek van Schaijk <arek@alternet.nl>
 */
trait RepositoryTrait
{

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Gets the Repository
     *
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Sets the Repository
     *
     * @param Repository $repository
     * @return void
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Gets the Project Key
     *
     * @return string
     */
    public function getProjectKey()
    {
        return $this->getRepository()->getProject()->getKey();
    }

}