<?php
namespace AlterNET\Cli\Command\Local;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Utility\StringUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocalVariablesCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalVariablesCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('local:variables');
        $this->setDescription('Shows all environment variables');
        $this->addFilterOption();
        $this->addCropOption();
        $this->addOption('show-passwords', null, InputOption::VALUE_NONE, 'Show passwords');
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
        $variables = $_SERVER;
        unset($variables['argv']);
        $rows = [];
        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $this->io->note('Variable "' . $key . '" could not be displayed since the value is of type: Array');
            } else {
                if ($this->passItemsThroughFilter([$key, $value])) {
                    // Hides passwords
                    if (!$input->getOption('show-passwords') && strpos(strtolower($key), 'pass') !== false) {
                        $value = '<info>[hidden]</info>';
                    } else {
                        // Crops the value
                        $value = StringUtility::crop($value, (int)$input->getOption('crop'));
                    }
                    $rows[] = [
                        $this->highlightFilteredWords($key),
                        $this->highlightFilteredWords($value)
                    ];
                }
            }
        }
        $count = count($rows);
        $this->renderFilter($count);
        if ($count) {
            $headers = [
                'Key', 'Value'
            ];
            $this->io->table($headers, $rows);

        }
    }

}