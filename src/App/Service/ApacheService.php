<?php
namespace AlterNET\Cli\App\Service;

use AlterNET\Cli\App;
use AlterNET\Cli\App\Service\Apache\Log\Error;

/**
 * Class ApacheService
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ApacheService implements AppServiceInterface
{

    /**
     * @var App
     */
    protected $app;

    /**
     * @var \SplObjectStorage|null
     */
    protected $errors;

    /**
     * Git constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Has Error Log
     *
     * @return bool
     */
    public function hasErrorLog()
    {
        return file_exists($this->app->getErrorLogFilePath());
    }

    /**
     * Gets the Errors
     *
     * @param int $limit
     * @return Error[]
     */
    public function getErrors($limit = 100)
    {
        return array_reverse($this->parseErrorLog($limit));
    }

    /**
     * Parses the Error Log
     *
     * @param int $limit
     * @return Error[]
     */
    protected function parseErrorLog($limit = 0)
    {
        $errors = [];
        if ($this->hasErrorLog()) {
            $contents = trim(file_get_contents($this->app->getErrorLogFilePath()));
            if (!empty($contents)) {
                $rows = array_reverse(explode(PHP_EOL, $contents));
                foreach ($rows as $row) {
                    if ($limit && count($errors) === $limit) {
                        break;
                    }
                    $regex = '/^\[([^\]]+)\] \[([^\]]+)\] (?:\[client ([^\]]+)\])?\s*(.*)$/i';
                    preg_match($regex, $row, $matches);
                    list($string, $date, $severity, $client, $message) = $matches;
                    $row = new Error();
                    $row->setTimestamp((is_string($date) ? strtotime($date) : 0));
                    $row->setSeverity($severity);
                    $row->setClient($client);
                    $row->setMessage($message);
                    $errors[] = $row;
                    unset($string);
                }
            }
        }
        return $errors;
    }

}