<?php
namespace AlterNET\Cli\Command\Local;

use AlterNET\Cli\App\Traits;
use AlterNET\Cli\Command\CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocalHostsDeleteCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalUpdateTemplatesCommand extends CommandBase
{

    use Traits\Local\TemplateServiceTrait;

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('local:updatetemplates');
        $this->setDescription('Updates the templates');
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getTemplateService()->update();
        $this->io->success('Successfully updated.');
    }

}