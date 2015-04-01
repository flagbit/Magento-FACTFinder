<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * This implementation retrieves the FACT-Finder data by using the cURL's "multi
 * interface".
 * Responses are queried in parallel and lazily and are cached as long as
 * parameters don't change.
 */
class MultiCurlDataProvider extends AbstractDataProvider
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \FACTFinder\Util\CurlInterface
     */
    protected $curl;

    protected $defaultCurlOptions;
    protected $necessaryCurlOptions;

    /**
     * @var bool
     * This is used to issue a warning if not all connections are being fetched
     * at the same time.
     */
    protected $usedBefore;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Util\CurlInterface $curl,
        UrlBuilder $urlBuilder
    ) {
        parent::__construct($loggerClass, $configuration);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->urlBuilder = $urlBuilder;

        $this->curl = $curl;

        $this->defaultCurlOptions = array(
            CURLOPT_CONNECTTIMEOUT => $this->configuration->getDefaultConnectTimeout(),
            CURLOPT_TIMEOUT        => $this->configuration->getDefaultTimeout(),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING       => '',
        );

        $this->necessaryCurlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
        );
    }

    public function setConnectTimeout($id, $timeout)
    {
        if (!isset($this->connectionData[$id]))
            throw new \InvalidArgumentException('Tried to set timeout for invalid ID $id.');

        $this->connectionData[$id]->setConnectionOption(
            CURLOPT_CONNECTTIMEOUT,
            $timeout
        );
    }

    public function setTimeout($id, $timeout)
    {
        if (!isset($this->connectionData[$id]))
            throw new \InvalidArgumentException('Tried to set timeout for invalid ID $id.');

        $this->connectionData[$id]->setConnectionOption(
            CURLOPT_TIMEOUT,
            $timeout
        );
    }

    // TODO: Could this be refactored some more?
    public function loadResponse($id)
    {
        if (!isset($this->connectionData[$id]))
            throw new \InvalidArgumentException('Tried to get response for invalid ID $id.');

        // Although this implementation of the data provider generally loads all
        // all available responses at once (basically ignoring the $id
        // parameter), it will not do so if the requested $id does not actually
        // need a connection itself. This allows for other connections to be
        // deferred as long as possible.
        if (!$this->hasUrlChanged($id))
            return;

        $connectionData = $this->connectionData[$id];

        $action = $connectionData->getAction();
        if (empty($action))
        {
            $this->log->error('Request type missing.');
            $connectionData->setNullResponse();
            return;
        }

        // From now on we ignore what parameter has been passed in and just load
        // all available responses.
        $connectionsToFetch = array();
        foreach ($this->connectionData as $id => $connectionData)
        {
            $action = $connectionData->getAction();
            if (!$this->hasUrlChanged($id)
                || empty($action)
            ) {
                continue;
            }

            $httpHeaderFields = $this->prepareHttpHeaders($connectionData);
            $parameters = $this->prepareParameters($connectionData);
            $url = $this->prepareConnectionOptions(
                $connectionData,
                $httpHeaderFields,
                $parameters
            );

            $connectionsToFetch[$id] = array(
                'connection' => $connectionData,
                'url' => $url
            );
        }

        if (count($connectionsToFetch))
        {
            if ($this->usedBefore)
                $this->log->warn('loadResponse() has been called before. You should try to configure all requests '
                               . 'before loading the first response so that all connections can be made in parallel.');

            $this->retrieveResponses($connectionsToFetch);

            $this->usedBefore = true;
        }
    }

    private function prepareHttpHeaders($connectionData)
    {
        $httpHeaderFields = clone $connectionData->getHttpHeaderFields();

        $language = $this->configuration->getLanguage();
        if (!empty($language))
            $httpHeaderFields['Accept-Language'] = $language;

        return $httpHeaderFields;
    }

    private function prepareParameters($connectionData)
    {
        $parameters = clone $connectionData->getParameters();

        if ($this->configuration->isDebugEnabled())
            $parameters['verbose'] = 'true';

        return $parameters;
    }

    private function prepareConnectionOptions(
        $connectionData,
        $httpHeaderFields,
        $parameters
    ) {
        if ($this->configuration->isDebugEnabled()
            && isset($_SERVER['HTTP_REFERER'])
            && !$connectionData->issetConnectionOption(CURLOPT_REFERER)
        ) {
            $connectionData->setConnectionOption(
                CURLOPT_REFERER,
                $_SERVER['HTTP_REFERER']
            );
        }

        $connectionData->setConnectionOption(
            CURLOPT_HTTPHEADER,
            $httpHeaderFields->toHttpHeaderFields()
        );

        $url = $this->urlBuilder->getAuthenticationUrl(
            $connectionData->getAction(),
            $parameters
        );

        $connectionData->setConnectionOption(CURLOPT_URL, $url);

        // Return non-authentication URL for reproducability.
        return $this->urlBuilder->getNonAuthenticationUrl(
            $connectionData->getAction(),
            $parameters
        );
    }

    private function retrieveResponses($connectionsToFetch)
    {
        $curl = $this->curl;

        $multiHandle = $curl->multi_init();
        if ($multiHandle === false)
        {
            $this->log->error("curl_multi_init() did not return a multi handle. "
                            . 'Setting a empty responses...');
            foreach ($connectionsToFetch as $id => $data)
            {
                $data['connection']->setResponse(
                    FF::getInstance('Core\Server\NullResponse'),
                    $data['url']
                );
            }
        }

        // Use a reference so that we can add a 'handle' field in the loop.
        foreach ($connectionsToFetch as $id => &$data)
        {
            $data['handle'] = $curl->init();

            if ($data['handle'] === false)
            {
                $this->log->error("curl_init() did not return a handle for ID $id. "
                                . 'Setting an empty response...');
                $data['connection']->setResponse(
                    FF::getInstance('Core\Server\NullResponse'),
                    $data['url']
                );
            }

            // We cannot use array_merge() here, because that does not preserve
            // numeric keys. So we use array union instead. However, as opposed
            // to array_merge() the left-hand operator's keys will be preserved.
            $curlOptions = $this->necessaryCurlOptions
                         + $data['connection']->getConnectionOptions()
                         + $this->defaultCurlOptions;

            $curl->setopt_array(
                $data['handle'],
                $curlOptions
            );

            $curl->multi_add_handle($multiHandle, $data['handle']);
        }
        unset($data); // Otherwise the reference remains and reusing the
                      // variable name $data further down this function will
                      // change the last element of the array.

        do
        {
            $status = $curl->multi_exec($multiHandle, $still_running);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $status == CURLM_OK)
        {
            // curl_multi_select sometimes returns -1 indefinitely in which case
            // the usual curl multi loop would run endlessly. This is due to the
            // underlying cURL library in C, which returns -1 upon errors which
            // cannot be checked from the outside. Simply waiting for 100 ms is
            // the suggested workaround. For further information see:
            // https://bugs.php.net/bug.php?id=61141
            // https://bugs.php.net/bug.php?id=63411
            // https://bugs.php.net/bug.php?id=63842
            // http://curl.haxx.se/libcurl/c/curl_multi_fdset.html
            if ($curl->multi_select($multiHandle) == -1)
                usleep(100);

            do
            {
                $status = $curl->multi_exec($multiHandle, $still_running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);

            // TODO: Fetch responses within this loop as they are ready (while
            // the others are still loading).
        }

        if ($status != CURLM_OK)
            $this->log->error('There was a cURL error: ' . $status);

        while (($msg = $curl->multi_info_read($multiHandle)) !== false)
        {
            // We do not check the value of $msg['msg'], because currently this
            // will always be CURLMSG_DONE.
            $curlErrorNumber = $msg['result'];

            // Set $data to the data array corresponding to the current handle.
            foreach ($connectionsToFetch as $data)
                if ($data['handle'] === $msg['handle'])
                    break;

            // We could skip multi_getcontent if $curlErrorNumber is different
            // from CURLE_OK, as it will always return null.
            $responseText = $curl->multi_getcontent($data['handle']);
            $httpCode = (int)$curl->getinfo($data['handle'], CURLINFO_HTTP_CODE);
            $curlError = $curl->error($data['handle']);

            $curl->multi_remove_handle($multiHandle, $data['handle']);
            $this->curl->close($data['handle']);

            $response = FF::getInstance('Core\Server\Response',
                $responseText,
                $httpCode,
                $curlErrorNumber,
                $curlError
            );

            $data['connection']->setResponse($response, $data['url']);
            $this->logResult($response);
        }
    }

    private function logResult($response)
    {
        $httpCode = $response->getHttpCode();
        $curlError = $response->getConnectionError();
        if ($httpCode >= 400) {
            $this->log->error("Connection failed. HTTP code: $httpCode");
        } else if ($httpCode == 0) {
            $this->log->error("Connection refused. cURL error: $curlError");
        } else if (floor($httpCode / 100) == 2) { // all successful status codes (2**)
            $this->log->info("Request successful!");
        }
    }

    private function hasUrlChanged($id)
    {
        $connectionData = $this->connectionData[$id];

        if (FF::isInstanceOf($connectionData->getResponse(), 'Core\Server\NullResponse'))
            return true;

        $url = $this->urlBuilder->getNonAuthenticationUrl(
            $connectionData->getAction(),
            $this->prepareParameters($connectionData)
        );

        return $url != $connectionData->getPreviousUrl();
    }
}
