<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;

/**
 * Class AppShareCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppShareCommand extends CommandBase
{

    /**
     * Configure
     *
     *
     */
    protected function configure()
    {
        $this->setName('app:share');
        $this->setDescription('Shares the contents of a file on HipChat');
    }

}