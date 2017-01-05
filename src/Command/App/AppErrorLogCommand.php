<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\StringUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppErrorLogCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppErrorLogCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:errorlog');
        $this->setDescription('Shows the latest errors from the Apache error log');
        $this->addFilterOption();
        $this->addCropOption(50);
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limits the given', 20);
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
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();
        if ($app->apache()->hasErrorLog()) {
            if (($errors = $app->apache()->getErrors((int)$this->input->getOption('limit')))) {
                $rows = [];
                foreach ($errors as $error) {
                    $date = str_replace(
                        [date('d-m-Y'), date('d-m-Y', strtotime('-1 day'))],
                        ['Today', 'Yesterday'],
                        $error->getDate('d-m-Y H:i:s')
                    );
                    if ($this->passItemsThroughFilter([$date, $error->getSeverity(), $error->getMessage()])) {
                        $rows[] = [
                            $this->highlightFilteredWords($date),
                            $this->highlightFilteredWords($error->getSeverity()),
                            $this->highlightFilteredWords(StringUtility::crop($error->getMessage(), (int)$input->getOption('crop')))
                        ];
                    }
                }
                $count = count($rows);
                if ($count) {
                    $headers = ['Date', 'Severity', 'Message'];
                    $this->renderFilter($count);
                    $this->io->table($headers, $rows);
                }
            }
        } else {
            $this->io->warning('The application has no error log.');
        }
    }

}