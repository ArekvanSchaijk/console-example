<?php
namespace AlterNET\Cli\Command;

use AlterNET\Cli\Config;
use AlterNET\Cli\Container\CrowdContainer;
use AlterNET\Cli\Driver\BambooDriver;
use AlterNET\Cli\Driver\BitbucketDriver;
use AlterNET\Cli\Driver\HipChatDriver;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\ConsoleUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
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
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

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
     * @var BambooDriver
     */
    protected $bambooDriver;

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
        $this->input = $input;
        $this->output = $output;
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
        $this->addOption('filter-no-count', null, InputOption::VALUE_NONE,
            'Prevents the filter from outputting the count.');
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

    /**
     * Prevent Not Being In An App
     * This prevents the command to run if it is not inside an app
     *
     * @return void
     */
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
            $remember = false;
            // Retrieving the credentials from the local configuration file
            $username = $this->config->local()->getCrowdUsername();
            $password = $this->config->local()->getCrowdPassword();
            if (!$username) {
                $remember = true;
                $this->io->note($openingsMessage);
                // This asks the user for the crowd username
                $username = $this->askCrowdUsername();
                // This asks the user for the crowd password
                $password = $this->askCrowdPassword();
            } elseif (!$password) {
                $remember = true;
                $this->io->note($openingsMessage);
                // This asks the user for the crowd username
                $username = $this->askCrowdUsername($username);
                // This asks the user for the crowd password
                $password = $this->askCrowdPassword();
            }
            // Asks the user if the CLI should remember the credentials
            if ($remember) {
                if ($this->io->confirm('Would you like the CLI to remember your credentials?', false)) {
                    $this->config->local()->setCrowdUsername($username);
                    $this->config->local()->setCrowdPassword($password);
                    $this->config->local()->write();
                }
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
     * @param array $values
     * @return bool
     */
    protected function passItemsThroughFilter(array $values)
    {
        if ((bool)$this->input->getOption('filter')) {
            foreach ($values as $value) {
                if (stripos($value, $this->input->getOption('filter')) !== false) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Highlight
     *
     * @param string $value
     * @return string
     */
    protected function highlightFilteredWords($value)
    {
        if (!(bool)$this->input->getOption('filter')) {
            return $value;
        }
        return preg_replace('/(\S*' . $this->input->getOption('filter') . '\S*)/i', '<comment>$1</comment>', $value);
    }

    /**
     * Renders the Filter
     *
     * @param int|null $results
     * @param bool $addQuery
     * @return void
     */
    protected function renderFilter($results = null, $addQuery = true)
    {
        if (!$this->input->getOption('filter-no-count')) {
            if ((bool)$this->input->getOption('filter')) {
                if ($results === null || $results > 0) {
                    $this->io->block(($results ? $results . ' ' : null) . 'Filtered result(s)' .
                        ($addQuery ? ' for "' . $this->input->getOption('filter') . '"' : null) . ':');
                } else {
                    $this->io->note('There are no filtered results found. You may want to remove or change the filters'
                        . ' value.');
                }
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
     * Bamboo Driver
     *
     * @return BambooDriver
     */
    protected function bambooDriver()
    {
        if (!$this->bambooDriver) {
            $this->bambooDriver = new BambooDriver(
                $this->processCollectCrowdCredentials()
            );
        }
        return $this->bambooDriver;
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

    /**
     * Runs a Command
     *
     * @param string $command
     * @param array $arguments
     * @return void
     */
    protected function runCommand($command, array $arguments)
    {
        $command = $this->getApplication()->find($command);
        $command->run(new ArrayInput(array_merge(['command' => $command], $arguments)), $this->output);
    }

}