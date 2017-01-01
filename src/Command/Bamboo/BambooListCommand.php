<?php
namespace AlterNET\Cli\Command\Bamboo;

use AlterNET\Cli\Command\CommandBase;
use ArekvanSchaijk\BambooServerClient\Api\Entity\Plan;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BambooListCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class BambooListCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bamboo:list');
        $this->setDescription('Lists all projects');
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
        $bamboo = $this->bambooDriver()->getApi();

        /* @var Plan $plan */
        foreach ($bamboo->getPlans() as $plan)
        {
            if ($plan->getKey() === 'GEMZST-DEV') {
                $bamboo->queuePlan($plan);
            }
        }



    }

}