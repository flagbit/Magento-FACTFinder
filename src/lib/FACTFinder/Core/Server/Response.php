<?php
namespace FACTFinder\Core\Server;

/**
 * Represents a request response from the FACT-Finder server.
 */
class Response
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var int
     */
    private $connectionErrorCode;

    /**
     * @var string
     */
    private $connectionError;

    public function __construct(
        $content,
        $httpCode,
        $connectionErrorCode,
        $connectionError
    ) {
        $this->content = $content;
        $this->httpCode = $httpCode;
        $this->connectionErrorCode = $connectionErrorCode;
        $this->connectionError = $connectionError;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return int
     */
    public function getConnectionErrorCode()
    {
        return $this->connectionErrorCode;
    }

    /**
     * @return string
     */
    public function getConnectionError()
    {
        return $this->connectionError;
    }
}
