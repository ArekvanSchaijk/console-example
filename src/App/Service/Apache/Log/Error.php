<?php
namespace AlterNET\Cli\App\Service\Apache\Log;

/**
 * Class Error
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class Error
{

    /**
     * @var string
     */
    protected $string;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $client;

    /**
     * @var string
     */
    protected $severity;

    /**
     * @var string
     */
    protected $message;

    /**
     * Gets the Date
     *
     * @param string $format
     * @return false
     */
    public function getDate($format)
    {
        return date($format, $this->getTimestamp());
    }

    /**
     * Gets the String
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Sets the String
     *
     * @param string $string
     */
    public function setString($string)
    {
        $this->string = trim($string);
    }

    /**
     * Gets the Timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets the Timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Gets the Client
     *
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets the Client
     *
     * @param string $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Gets the Severity
     *
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Sets the Severity
     *
     * @param string $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    /**
     * Gets the Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the Message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

}