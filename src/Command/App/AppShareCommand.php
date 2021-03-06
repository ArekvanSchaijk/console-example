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
use Symfony\Component\Console\Input\InputOption;
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
        $this->addOption('room', null, InputOption::VALUE_REQUIRED, 'The HipChat room (room id or room name)');
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
            'config' => [
                'method' => 'executeCategoryConfig',
                'description' => 'Shares the application config file in the room.'
            ],
            'error_log' => [
                'method' => 'executeErrorLog',
                'description' => 'Shares the last 5 lines of the error log.'
            ],
            'important_message' => [
                'method' => 'executeCategoryImportantMessage',
                'description' => 'Send a important message to the room.',
            ],
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
     * @throws Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This prevents that the command is being executed outside an app
        $this->preventNotBeingInAnApp();
        // This loads the app where we are in (working directory)
        $app = AppUtility::load();
        // Gets the room id
        $roomId = $this->collectHipChatRoomId($app);
        // This checks if the app has a HipChat integration (if the room id is known)
        if (!$roomId) {
            $this->io->error('The application has no HipChat configuration.');
            exit;
        }
        $category = null;
        $categories = self::getCategories();
        // Gets the input argument 'subject'
        $subject = trim($input->getArgument('subject'));
        // Offers a list of categories to choose from if the argument 'subject' is missing
        if (!$subject) {
            $category = $this->io->choice('What do you like to share on HipChat?', self::getCategoryChoices());
        } elseif (StringUtility::getFirstCharacter($subject) === '[') {
            $category = $category = str_replace(['[', ']'], null, $subject);
        }
        // Checks if the subject is a category, otherwise we interpret the 'subject' as a path to a file
        if ($category) {
            if (!isset($categories[$category])) {
                $this->io->error('The given category [' . $category . '] does not exists.');
                exit;
            }
            $method = $categories[$category]['method'];
            // Executes the category method
            if (method_exists($this, $method)) {
                $this->$method($app, $roomId);
            } else {
                throw new Exception('The method belonging to the category [' . $category . '] does not exists.');
            }
        } else {
            $filePath = getcwd() . '/' . $subject;
            // Absolute file path from app's working directory
            if (StringUtility::isAbsolutePath($subject)) {
                $filePath = $app->getWorkingDirectory() . $subject;
            }
            $this->hipChatFileContent($roomId, $filePath);
        }
    }

    /**
     * Collects the HipChat RoomId
     *
     * @param App $app
     * @return int|null
     */
    protected function collectHipChatRoomId(App $app)
    {
        $roomId = ($app->getConfig()->getHipChatRoomId() ?: null);
        if (($search = $this->input->getOption('room'))) {
            if (ctype_digit($search)) {
                $roomId = $this->hipChatDriver()->getRoom($search)->getId();
            } else {
                $rooms = $this->hipChatDriver()->getRooms($search);
                if ($rooms) {
                    if (count($rooms) === 1) {
                        $roomId = $rooms[0]->getId();
                    } else {
                        $choices = [];
                        foreach ($rooms as $room) {
                            $choices[$room->getName()] = $room->getId();
                        }
                        $roomId = $this->io->choice('Select the room where you would like to share', $choices);
                    }
                } else {
                    $this->io->error('Could not find any room with "' . $search . '".');
                    exit;
                }
            }
        }
        return $roomId;
    }

    /**
     * Executes the Error Log
     *
     * @param App $app
     * @param int $roomId
     * @return void
     */
    protected function executeErrorLog($app, $roomId)
    {
        if ($app->apache()->hasErrorLog()) {
            $this->hipChatFileContent($roomId, null, $app->apache()->getString(5));
        } else {
            $this->io->error('The application has no error log.');
            exit;
        }
    }

    /**
     * Executes the Category Config
     *
     * @param App $app
     * @param int $roomId
     * @return void
     */
    protected function executeCategoryConfig(App $app, $roomId)
    {
        $this->hipChatFileContent($roomId, $app->getConfigFilePath());
    }

    /**
     * Executes the Important Message
     *
     * @param App $app
     * @param int $roomId
     * @return void
     */
    protected function executeCategoryImportantMessage(App $app, $roomId)
    {
        if (($setMessage = $this->io->ask('Set the message'))) {
            // Makes a new HipChat message
            $message = HipChatDriver::createMessage();
            $message->setColor(Message::COLOR_PURPLE);
            $message->setMessage($setMessage);
            $message->setNotify(true);
            $message->setFrom('Important message');
            // And sends it
            $this->hipChatDriver()->sendMessage($message, $roomId);
            $this->io->success('The message was sent successfully.');
        }
    }

    /**
     * HipChat File Content
     *
     * @param int $roomId
     * @param string|null $filePath
     * @param string|null $content
     * @return void
     */
    protected function hipChatFileContent($roomId, $filePath = null, $content = null)
    {
        if (is_null($content)) {
            // Shows an error if the file does not exists or the path is not a file
            if (!file_exists($filePath)) {
                $this->io->error('The file "' . $filePath . '" does not exists.');
                exit;
            } elseif (!is_file($filePath)) {
                $this->io->error('"' . $filePath . '" is not a file.');
                exit;
            }
            $content = file_get_contents($filePath);
        }
        // Gets the contents of the file and binds it in a new HipChat message
        $message = HipChatDriver::createMessage();
        $message->setMessage('/code ' . $content);
        $message->setColor(Message::COLOR_YELLOW);
        // And sends it
        $this->hipChatDriver()->sendMessage($message, $roomId);
        $this->io->success('The file is shared successfully on HipChat.');
    }

}