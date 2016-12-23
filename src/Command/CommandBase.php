<?php
namespace AlterNET\Cli\Command;

use AlterNET\Cli\App;
use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use AlterNET\Cli\Driver\BitbucketDriver;
use AlterNET\Cli\Driver\HipChatDriver;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
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
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CrowdContainer
     */
    protected $crowdContainer;

    /**
     * @var BitbucketDriver
     */
    protected $bitbucketDriver;

    /**
     * @var HipChatDriver
     */
    protected $hipChatDriver;

    /**
     * CommandBase constructor.
     * @param string|null $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->config = ConsoleUtility::getConfig();
    }

    /**
     * Initialize
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->io = new SymfonyStyle($input, $output);
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
     * Prevents Being Within App
     * This prevents the command to run if it is inside an app
     *
     * @return void
     */
    protected function preventBeingWithinAnApp()
    {
        if (AppUtility::isCwdInApp()) {
            $this->io->error('It\'s not possible to get an app inside the directory of an existing app. '
                . 'Please browse to your web root directory and try again.');
            exit;
        }
    }

    protected function preventNotBeingInAnApp()
    {
        if (!AppUtility::isCwdInApp()) {
            $this->io->error('This command can only be used within the directory of an application.');
            exit;
        }
    }

    /**
     * Process Collects the Crowd Credentials
     *
     * @return CrowdContainer
     */
    protected function processCollectCrowdCredentials()
    {
        if (!$this->crowdContainer) {
            $openingsMessage = 'Please login with your Crowd credentials';
            if (!isset($_SERVER['ALTERNET_CLI_USERNAME']) || empty(trim($_SERVER['ALTERNET_CLI_USERNAME']))) {
                $this->io->note($openingsMessage);
                $username = $this->askCrowdUsername();
                $password = $this->askCrowdPassword();
            } elseif (!isset($_SERVER['ALTERNET_CLI_PASSWORD']) || empty(trim($_SERVER['ALTERNET_CLI_PASSWORD']))) {
                $this->io->note($openingsMessage);
                $username = $this->askCrowdUsername(trim($_SERVER['ALTERNET_CLI_USERNAME']));
                $password = $this->askCrowdPassword();
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
     * Asks the Crowd Username
     *
     * @param string|null $default
     * @return string
     */
    protected function askCrowdUsername($default = null)
    {
        return $this->io->ask('Username', $default, function ($value) {
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
     * @return string
     */
    protected function askCrowdPassword()
    {
        return $this->io->askHidden('Password', function ($value) {
            $value = trim($value);
            if (empty($value)) {
                throw new \Exception('The given password can\'t be empty');
            }
            return $value;
        });
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
                $this->io->block(($results ? $results . ' ' : null) . 'Filtered result(s)' .
                    ($addQuery ? ' for "' . $input->getOption('filter') . '"' : null) . ':');
            } else {
                $this->io->note('There are no filtered results found. You may want to remove or change the filters value.');
            }
        }
    }

    /**
     * Bitbucket Driver
     *
     * @return BitbucketDriver
     */
    protected function bitbucketDriver()
    {
        if (!$this->bitbucketDriver) {
            $this->bitbucketDriver = new BitbucketDriver(
                $this->processCollectCrowdCredentials()
            );
        }
        return $this->bitbucketDriver;
    }

    /**
     * HipChat Driver
     *
     * @return HipChatDriver
     */
    protected function hipChatDriver()
    {
        if (!$this->hipChatDriver) {
            $this->hipChatDriver = new HipChatDriver();
        }
        return $this->hipChatDriver;
    }

}