<?php
namespace AlterNET\Cli\Command\Bitbucket\App;

use AlterNET\Cli\App\TemporaryApp;
use AlterNET\Cli\App\Traits;

/**
 * Class CreateRepoApp
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class CreateRepoApp extends TemporaryApp
{

    use Traits\Bitbucket\RepositoryTrait;

}