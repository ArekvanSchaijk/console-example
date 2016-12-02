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
    public function configure()
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
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $warnings = [];
        $lines = [[
            'Key', 'Value'
        ]];
        $variables = $_SERVER;
        unset($variables['argv']);
        foreach ($variables as $key => $value) {
            $values = [
                $key,
                $value
            ];
            if (is_array($value)) {
                $warnings[] = '<comment>Warning: Variable "' . $key . '" could not be displayed ' .
                    'since the value is of type: Array</comment>';
            } else {
                if ($this->passItemsThroughFilter($input, $values)) {
                    // Hides passwords
                    if (!$input->getOption('show-passwords') && strpos(strtolower($key), 'pass') !== false) {
                        $value = '<info>[hidden]</info>';
                    }
                    // Crops the value
                    $value = StringUtility::crop($value, (int)$input->getOption('crop'));
                    $lines[] = [
                        $this->highlightFilteredWords($input, $key),
                        $this->highlightFilteredWords($input, $value)
                    ];
                }
            }
        }
        $count = count($lines) - 1;
        $this->renderFilter($input, $output, $count);
        if ($count) {
            $this->renderArrayAsTable($output, $lines);
        }
        if ($warnings) {
            foreach ($warnings as $warning) {
                $output->writeln($warning);
            }
        }
    }

}