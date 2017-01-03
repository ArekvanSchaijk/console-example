<?php
namespace AlterNET\Cli\App\Apache;

/**
 * Class ErrorLog
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ErrorLog
{

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