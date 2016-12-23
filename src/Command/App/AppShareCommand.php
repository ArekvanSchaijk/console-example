<?php
namespace AlterNET\Cli\Command\App;

use AlterNET\Cli\App;
use AlterNET\Cli\Command\CommandBase;
use AlterNET\Cli\Driver\HipChatDriver;
use AlterNET\Cli\Exception;
use AlterNET\Cli\Utility\AppUtility;
use AlterNET\Cli\Utility\StringUtility;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppShareCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class AppShareCommand extends CommandBase
{

    /**
     * @var App
     */
    protected $app;

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:share');
        $this->setDescription('Share things about the application on HipChat');
        $this->addArgument('subject', InputArgument::OPTIONAL, 'Relative file path or an option between brackets ([..])');
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
            'config' => 'executeCategoryConfig',
        ];
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app we're working with
        $app = AppUtility::load();
        // This checks if the app has a HipChat integration (if the room id is known)
        if (!$app->getConfig()->getHipChatRoomId()) {
            $this->io->error('The application has no HipChat configuration.');
            exit;
        }
        $category = null;
        $categories = self::getCategories();
        // Gets the input argument 'subject'
        $subject = trim($input->getArgument('subject'));
        // Offers a list of categories to choose from if the argument 'subject' is missing
        if (!$subject) {
            $category = $this->io->choice('What do you want to share on HipChat?', $categories);
        } elseif (StringUtility::getFirstCharacter($subject) === '[') {
            $category = $category = str_replace(['[', ']'], null, $subject);
        }
        // Checks if the subject is a category, otherwise we interpret the 'subject' as a path to a file
        if ($category) {
            if (!isset($categories[$category])) {
                $this->io->error('The given category [' . $category . '] does not exists.');
                exit;
            }
            $method = $categories[$category];
            // Executes the category method
            if (method_exists($this, $method)) {
                $this->$method($input, $output, $app);
            } else {
                throw new Exception('The method belonging to the category [' . $category . '] does not exists.');
            }
        } else {
            $filePath = getcwd() . '/' . $subject;
            // Absolute file path from app's working directory
            if (StringUtility::getFirstCharacter($subject) === '/') {
                $filePath = $app->getWorkingDirectory() . $subject;
            }
            $this->hipChatFileContent($filePath, $app->getConfig()->getHipChatRoomId());
        }
    }

    /**
     * Executes the Category Config
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param App $app
     * @return void
     */
    protected function executeCategoryConfig(InputInterface $input, OutputInterface $output, App $app)
    {
        $this->hipChatFileContent($app->getConfigFilePath(), $app->getConfig()->getHipChatRoomId());
    }

    /**
     * HipChat File Content
     *
     * @param string $filePath
     * @param int $roomId
     * @return void
     */
    protected function hipChatFileContent($filePath, $roomId)
    {
        // Shows an error if the file does not exists or the path is not a file
        if (!file_exists($filePath)) {
            $this->io->error('The file "' . $filePath . '" does not exists.');
            exit;
        } elseif (!is_file($filePath)) {
            $this->io->error('"' . $filePath . '" is not a file.');
            exit;
        }
        // Gets the contents of the file and binds it in a new HipChat message
        $message = HipChatDriver::createMessage();
        $message->setMessage('/code ' . file_get_contents($filePath));
        $message->setColor(Message::COLOR_YELLOW);
        // And sends it
        $this->hipChatDriver()->sendMessage($message, $roomId);
        $this->io->success('The file is shared successfully on HipChat.');
    }

}