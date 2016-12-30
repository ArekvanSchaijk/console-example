<?php
namespace AlterNET\Cli\Command\Local;

use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Config\LocalConfig;
use AlterNET\Cli\Exception;
use AlterNET\Package\Environment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LocalConfigureCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class LocalConfigureCommand extends CommandBase
{

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('local:configure');
        $this->setDescription('Configures the CLI');
        $this->addArgument('category', InputArgument::OPTIONAL, 'The category to edit');
        $this->addOption('username', 'U', InputOption::VALUE_REQUIRED, 'The username of the category you want to configure');
        $this->addOption('password', 'P', InputOption::VALUE_REQUIRED, 'The password of the category you want to configure');
    }

    /**
     * Gets the Categories
     *
     * @return array
     * @static
     */
    static protected function getCategories()
    {
        return [
            'options' => [
                'method' => 'configureOptions',
                'description' => 'Configures the CLI options',
            ],
            'mysql' => [
                'method' => 'configureMySql',
                'description' => 'Configures MySQL'
            ],
            'crowd' => [
                'method' => 'configureCrowd',
                'description' => 'Configures Crowd',
            ],
            'backups' => [
                'method' => 'configureBackup',
                'description' => 'Configures backups',
            ],
            'reset_mysql' => [
                'method' => 'resetMySql',
                'description' => 'Resets the MySQL configuration'
            ],
            'reset_crowd' => [
                'method' => 'resetCrowd',
                'description' => 'Resets the Crowd configuration'
            ],
            'reset_backups' => [
                'method' => 'resetBackups',
                'description' => 'Resets the backups configuration'
            ]
        ];
    }

    /**
     * Gets the Category Choices
     *
     * @return array
     * @static
     */
    static protected function getCategoryChoices()
    {
        $choices = [];
        foreach (self::getCategories() as $category => $value) {
            $choices[$category] = $value['description'];
        }
        return $choices;
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $category = null;
        $categories = self::getCategories();
        // This checks if the category is passed as an argument
        if (($category = $input->getArgument('category'))) {
            if (!isset($categories[$category])) {
                $category = null;
                $this->io->error('The given category "' . $category . '" does not exists.');
            }
        }
        // If there is no category selected then we offer a list of choices
        if (!$category) {
            $category = $this->io->choice('What do you want to configure?', self::getCategoryChoices());
        }
        // Executes the method belonging to the category
        $config = $this->config->local();
        $method = $categories[$category]['method'];
        if (method_exists($this, $method)) {
            $this->$method($config);
        } else {
            throw new Exception('The method belonging to the category ' . $category . ' does not exists.');
        }
        // And finally this writes the config
        $config->write();
        $this->io->success('The configuration is successfully written.');
    }

    /**
     * Configures the Options
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function configureOptions(LocalConfig $config)
    {
        if (Environment::isLocalEnvironment()) {
            $config->setOption(
                $config::OPTION_HOST_FILE_MANAGEMENT,
                $this->io->confirm('Enable Host File Management?', $config->isOptionHostFileManagement())
            );
        }
    }

    /**
     * Configures MySql
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function configureMySql(LocalConfig $config)
    {
        if (
            !$this->autoConfigureUsername(function ($username) use ($config) {
                $config->setMySqlUsername($username);
            })
            && !$this->autoConfigurePassword(function ($password) use ($config) {
                $config->setMySqlPassword($password);
            })
        ) {
            $config->setMySqlUsername(
                $this->io->ask('MySQL Username', ($config->getMySqlUsername() ?: null), function ($value) use ($config) {
                    if (!$value || $value === 'false') {
                        $value = $config->getDefaultMySqlUsername();
                    }
                    return $value;
                })
            );
            $config->setMySqlPassword(
                $this->io->ask('MySQL Password', ($config->getMySqlPassword() ?: null), function ($value) use ($config) {
                    if (!$value || $value === 'false') {
                        $value = $config->getDefaultMySqlPassword();
                    }
                    return $value;
                })
            );
        }
    }

    /**
     * Configures Crowd
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function configureCrowd(LocalConfig $config)
    {
        if (
            !$this->autoConfigureUsername(function ($username) use ($config) {
                $config->setCrowdUsername($username);
            })
            && !$this->autoConfigurePassword(function ($password) use ($config) {
                $config->setCrowdPassword($password);
            })
        ) {
            $config->setCrowdUsername(
                $this->io->ask('Crowd Username', ($config->getCrowdUsername() ?: null), function ($value) use ($config) {
                    if (!$value || $value === 'false') {
                        $value = $config->getDefaultCrowdUsername();
                    }
                    return $value;
                })
            );
            $config->setCrowdPassword(
                $this->io->ask('Crowd Password', ($config->getCrowdPassword() ?: null), function ($value) use ($config) {
                    if (!$value || $value === 'false') {
                        $value = $config->getDefaultCrowdPassword();
                    }
                    return $value;
                })
            );
        }
    }

    /**
     * Configure Backup
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function configureBackup(LocalConfig $config)
    {
        $config->setBackupPath(
            $this->io->ask(
                'Backup path',
                ($config->getBackupPath() ?: CLI_DEFAULT_BACKUP_PATH),
                function ($value) use ($config) {
                    if (!$value || $value === 'false' || $value === CLI_DEFAULT_BACKUP_PATH) {
                        $value = false;
                    } else {
                        $value = rtrim($value, '/\\');
                        if (!file_exists($value)) {
                            throw new Exception('The directory "' . $value . '" does not exists. Please create it'
                                . ' before you configure it.');
                        }
                    }
                    return $value;
                }
            )
        );
    }

    /**
     * Resets MySQL
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function resetMySql(LocalConfig $config)
    {
        $config->setMySqlUsername(
            $config->getDefaultMySqlUsername()
        );
        $config->setMySqlPassword(
            $config->getDefaultMySqlPassword()
        );
    }

    /**
     * Resets Crowd
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function resetCrowd(LocalConfig $config)
    {
        $config->setCrowdUsername(
            $config->getDefaultCrowdUsername()
        );
        $config->setCrowdPassword(
            $config->getDefaultCrowdPassword()
        );
    }

    /**
     * Resets the Backups
     *
     * @param LocalConfig $config
     * @return void
     */
    protected function resetBackups(LocalConfig $config)
    {
        $config->setBackupPath(
            $config->getDefaultBackupPath()
        );
    }

    /**
     * Auto Configures the Username
     *
     * @param callable $function
     * @return bool
     */
    protected function autoConfigureUsername(callable $function)
    {
        if (($username = $this->input->getOption('username'))) {
            $function($username);
            return true;
        }
        return false;
    }

    /**
     * Auto Configures the Password
     *
     * @param callable $function
     * @return bool
     */
    protected function autoConfigurePassword(callable $function)
    {
        if (($password = $this->input->getOption('password'))) {
            $function($password);
            return true;
        }
        return false;
    }

}