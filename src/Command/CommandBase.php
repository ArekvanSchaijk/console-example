<?php
namespace AlterNET\Cli\Command;

use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class CommandBase
 * @author Arek van Schaijk <arek@alternet.nl>
 */
abstract class CommandBase extends Command
{

    /**
     * @var Config
     */
    static protected $config;

    /**
     * @var CrowdContainer
     */
    protected $crowdContainer;

    /**
     * CommandBase constructor.
     * @param string|null $name
     */
    public function __construct($name = null)
    {
        self::$config = Config::load();
        parent::__construct($name);
    }

    /**
     * Adds a Filter Option
     *
     * @param string|null $description
     * @return void
     */
    protected function addFilterOption($description = null)
    {
        $description = ($description ?: 'Filters the result by a given value');
        $this->addOption('filter', 'f', InputOption::VALUE_REQUIRED, $description);
    }

    /**
     * Process Collects the Crowd Credentials
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return CrowdContainer
     */
    protected function processCollectCrowdCredentials(InputInterface $input, OutputInterface $output)
    {
        if (!$this->crowdContainer) {
            $openingsMessage = '<question>Please login with your Crowd credentials</question>';
            if (!isset($_SERVER['ALTERNET_CLI_USERNAME']) || empty(trim($_SERVER['ALTERNET_CLI_USERNAME']))) {
                $output->writeln($openingsMessage);
                $username = $this->askCrowdUsername($input, $output);
                $password = $this->askCrowdPassword($input, $output);
            } elseif (!isset($_SERVER['ALTERNET_CLI_PASSWORD']) || empty(trim($_SERVER['ALTERNET_CLI_PASSWORD']))) {
                $output->writeln($openingsMessage);
                $username = trim($_SERVER['ALTERNET_CLI_USERNAME']);
                $output->writeln('<info>Username: ' . $username . '</info>');
                $password = $this->askCrowdPassword($input, $output);
            } else {
                $username = trim($_SERVER['ALTERNET_CLI_USERNAME']);
                $password = trim($_SERVER['ALTERNET_CLI_PASSWORD']);
            }
            // Creates a new CrowdContainer and stores here the credentials
            $this->crowdContainer = new CrowdContainer();
            $this->crowdContainer->username = $username;
            $this->crowdContainer->password = $password;
        }
        return $this->crowdContainer;
    }

    /**
     * Destroys the CrowdContainer
     *
     * @return bool Returns true if the container was destroyed succesfully
     */
    protected function destroyCrowdContainer()
    {
        $hasContainer = (bool)$this->crowdContainer;
        unset($this->crowdContainer);
        return $hasContainer;
    }

    /**
     * Asks the Crowd Username
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function askCrowdUsername(InputInterface $input, OutputInterface $output)
    {
        /* @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Username: ');
        $question->setValidator(function ($value) {
            $value = trim($value);
            if (empty($value)) {
                throw new \Exception('The given username can\'t be empty');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        return $helper->ask($input, $output, $question);
    }

    /**
     * Asks the Crowd Password
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function askCrowdPassword(InputInterface $input, OutputInterface $output)
    {
        /* @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Password: ');
        $question->setValidator(function ($value) {
            $value = trim($value);
            if (empty($value)) {
                throw new \Exception('The given password can\'t be empty');
            }
            return $value;
        });
        $question->setHidden(true);
        $question->setHiddenFallback(true);
        $question->setMaxAttempts(3);
        return $helper->ask($input, $output, $question);
    }

    /**
     * Passes the Value Through the Filter
     *
     * @param InputInterface $input
     * @param array $values
     * @return bool
     */
    protected function passItemsThroughFilter(InputInterface $input, array $values)
    {
        if ((bool)$input->getOption('filter')) {
            foreach ($values as $value) {
                if (stripos($value, $input->getOption('filter')) !== false) {
                    return TRUE;
                }
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Highlight
     *
     * @param InputInterface $input
     * @param string $value
     * @return string
     */
    protected function highlightFilteredWords(InputInterface $input, $value)
    {
        if (!(bool)$input->getOption('filter')) {
            return $value;
        }
        return preg_replace('/(\S*' . $input->getOption('filter') . '\S*)/i', '<comment>$1</comment>', $value);
    }

    /**
     * Renders the Filter
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param int|null $results
     * @param bool $addQuery
     * @return void
     */
    protected function renderFilter(InputInterface $input, OutputInterface $output, $results = null, $addQuery = true)
    {
        if ((bool)$input->getOption('filter')) {
            if ($results === null || $results > 0) {
                $output->writeln('<info>' . ($results ? $results . ' ' : null) . 'Filtered result(s)' .
                    ($addQuery ? ' for "' . $input->getOption('filter') . '"' : null) . ':</info>');
            } else {
                $output->writeln('<comment>There are no filtered results found. You may want to remove or change the filter.</comment>');
            }
        }
    }

    /**
     * Renders an Array as a Table
     *
     * @param OutputInterface $output
     * @param array $array
     * @return void
     */
    protected function renderArrayAsTable(OutputInterface $output, array $array)
    {
        $table = new Table($output);
        $table->setHeaders(array_values($array)[0]);
        unset($array[0]);
        $table->setRows(array_values($array));
        $table->render();
    }

}