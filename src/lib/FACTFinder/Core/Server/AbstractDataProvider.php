<?php
namespace FACTFinder\Core\Server;

/**
 * Data providers take care of retrieving pre-configured requests with
 * FACT-Finder data, including the network connection if necessary.
 */
abstract class AbstractDataProvider
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var ConnectionData[] Keys are IDs to identify each connection data
     *      object.
     */
    protected $connectionData;

    /**
     * @var int
     */
    static private $nextID = 0;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;

        $this->connectionData = array();
    }

    /**
     * Make a connection data object known to the data provider and obtain an ID
     * for it (basically, a handle).
     *
     * @param ConnectionData $connectionData The connection data object to be
     *        registered.
     *
     * @param int The ID by which to refer to the connection data in the future.
     *
     * @throws InvalidArgumentException if the $id is already in use.
     */
    public function register(ConnectionData $connectionData)
    {
        $id = self::$nextID++;

        $this->connectionData[$id] = $connectionData;

        $this->log->debug("Registered connection data for ID $id.");

        return $id;
    }

    /**
     * Remove all references to the connection data object identified by $id.
     *
     * @param mixed $id The ID corresponding to the connection data object to be
     *        removed from the DataProvider.
     */
    public function unregister($id)
    {
        unset($this->connectionData[$id]);

        $this->log->debug("Unregistered connection data for ID $id.");
    }

    /**
     * Set the number of seconds to wait while trying to connect. Any particular
     * implementation of this abstract class is free to ignore this timeout, but
     * it should respect it if the underlying connection mechanism allows.
     *
     * @param mixed $id The ID of the connection data for which to set the
     *        timeout.
     * @param int $timeout The timeout in seconds.
     */
    abstract public function setConnectTimeout($id, $timeout);

    /**
     * Set the number of seconds to wait for the entire request to complete. Any
     * particular implementation of this abstract class is free to ignore this
     * timeout, but it should respect it if the underlying connection mechanism
     * allows.
     *
     * @param mixed $id The ID of the connection data for which to set the
     *        timeout.
     * @param int $timeout The timeout in seconds.
     */
    abstract public function setTimeout($id, $timeout);

    /**
     * Load a response based on the current state of the connection data
     * corresponding to $id and fill that ConnectionData object with this
     * response.
     *
     * @param mixed $id The ID of the connection data for which to retrieve a
     *        response.
     *
     * @return void The response is NOT returned by this function. It has to be
     *         obtained directly from the ConnectionData object.
     */
    abstract public function loadResponse($id);
}
