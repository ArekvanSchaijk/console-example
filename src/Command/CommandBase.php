<?php
namespace AlterNET\Cli\Command;

use AlterNET\Cli\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class CommandBase
 * @author Arek van Schaijk <info@ucreation.nl>
 */
abstract class CommandBase extends Command
{

    /**
     * @var Config
     */
    static protected $config;

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
     * Process Crowd Login
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function processCrowdLogin(InputInterface $input, OutputInterface $output)
    {
        $openingsMessage = 'Please login with your Crowd credentials';
        if (!isset($_SERVER['ALTERNET_CLI_USERNAME']) || empty(trim($_SERVER['ALTERNET_CLI_USERNAME']))) {
            $output->writeln($openingsMessage);
            $username = $this->askCrowdUsername($input, $output);
            $password = $this->askCrowdPassword($input, $output, $username);
        } elseif (!isset($_SERVER['ALTERNET_CLI_PASSWORD']) || empty(trim($_SERVER['ALTERNET_CLI_PASSWORD']))) {
            $output->writeln($openingsMessage);
            $username = trim($_SERVER['ALTERNET_CLI_USERNAME']);
            $output->writeln('Username: '. $username);
            $password = $this->askCrowdPassword($input, $output, $username);
        }
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
        $helper = $this->getHelper('question');
        $question = new Question('Username: ');
        $question->setValidator(function ($value) {
            if (empty(trim($value))) {
                throw new \Exception('The given username can\'t be empty');
            }
        });
        $question->setMaxAttempts(3);
        return trim($helper->ask($input, $output, $question));
    }

    /**
     * Asks the Crowd Password
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string|null $username
     * @return string
     */
    protected function askCrowdPassword(InputInterface $input, OutputInterface $output, $username = null)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Password for user ' . $username . ': ');
        $question->setValidator(function ($value) {
            if (empty(trim($value))) {
                throw new \Exception('The given password can\'t be empty');
            }
        });
        $question->setHidden(true);
        $question->setMaxAttempts(3);
        return trim($helper->ask($input, $output, $question));
    }

}