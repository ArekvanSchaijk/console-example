<?php
namespace AlterNET\Cli\Command;

use AlterNET\Cli\App\AppConfig;
use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     * @var AppConfig
     */
    static protected $appConfig;

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
        self::$config = Config::create();
        parent::__construct($name);
    }

    /**
     * Adds the Crop Option
     *
     * @param int $default
     * @param null $description
     * @return void
     */
    protected function addCropOption($default = 50, $description = null)
    {
        $description = ($description ?: 'Sets the crop length');
        $this->addOption('crop', null, InputOption::VALUE_OPTIONAL, $description, $default);
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
     * @param SymfonyStyle $io
     * @return CrowdContainer
     */
    protected function processCollectCrowdCredentials(SymfonyStyle $io)
    {
        if (!$this->crowdContainer) {
            $openingsMessage = 'Please login with your Crowd credentials';
            if (!isset($_SERVER['ALTERNET_CLI_USERNAME']) || empty(trim($_SERVER['ALTERNET_CLI_USERNAME']))) {
                $io->note($openingsMessage);
                $username = $this->askCrowdUsername($io);
                $password = $this->askCrowdPassword($io);
            } elseif (!isset($_SERVER['ALTERNET_CLI_PASSWORD']) || empty(trim($_SERVER['ALTERNET_CLI_PASSWORD']))) {
                $io->note($openingsMessage);
                $username = $this->askCrowdUsername($io, trim($_SERVER['ALTERNET_CLI_USERNAME']));
                $password = $this->askCrowdPassword($io);
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
     * @param SymfonyStyle $io
     * @param string|null $default
     * @return string
     */
    protected function askCrowdUsername(SymfonyStyle $io, $default = null)
    {
        return $io->ask('Username', $default, function ($value) {
            $value = trim($value);
            if (empty($value)) {
                throw new \Exception('The given username can\'t be empty');
            }
            return $value;
        });
    }

    /**
     * Asks the Crowd Password
     *
     * @param SymfonyStyle $io
     * @return string
     */
    protected function askCrowdPassword(SymfonyStyle $io)
    {
        return $io->askHidden('Password', function ($value) {
            $value = trim($value);
            if (empty($value)) {
                throw new \Exception('The given password can\'t be empty');
            }
            return $value;
        });
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
        $io = new SymfonyStyle($input, $output);
        if ((bool)$input->getOption('filter')) {
            if ($results === null || $results > 0) {
                $io->block(($results ? $results . ' ' : null) . 'Filtered result(s)' .
                    ($addQuery ? ' for "' . $input->getOption('filter') . '"' : null) . ':');
            } else {
                $io->note('There are no filtered results found. You may want to remove or change the filters value.');
            }
        }
    }

}