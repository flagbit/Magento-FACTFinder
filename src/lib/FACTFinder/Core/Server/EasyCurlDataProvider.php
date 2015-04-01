<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * This implementation retrieves the FACT-Finder data by using the "easy cURL
 * interface" (I didn't even make that up; that's what cURL itself calls it:
 * 'easy' as opposed to 'multi').
 * Responses are queried sequentially and lazily and are cached as long as
 * parameters don't change.
 */
class EasyCurlDataProvider extends AbstractDataProvider
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

        $httpHeaderFields = $this->prepareHttpHeaders($connectionData);
        $parameters = $this->prepareParameters($connectionData);
        $url = $this->prepareConnectionOptions(
            $connectionData,
            $httpHeaderFields,
            $parameters
        );

        $response = $this->retrieveResponse($connectionData);

        $connectionData->setResponse($response, $url);

        $this->logResult($response);
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
            && !$connectionData->issetConnectionOptions(CURLOPT_REFERER)
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

        return $url;
    }

    private function retrieveResponse($connectionData)
    {
        $curlHandle = $this->curl->init();
        if ($curlHandle === false)
        {
            $this->log->error("curl_init() did not return a handle for ID $id. "
                            . 'Setting an empty response...');
            return FF::getInstance('Core\Server\NullResponse');
        }

        // We cannot use array_merge() here, because that does not preserve
        // numeric keys. So we use array union instead. However, as opposed to
        // array_merge(), the left-hand operator's keys will be preserved.
        $curlOptions = $this->necessaryCurlOptions
                     + $connectionData->getConnectionOptions()
                     + $this->defaultCurlOptions;

        $this->curl->setopt_array(
            $curlHandle,
            $curlOptions
        );

        $responseText = $this->curl->exec($curlHandle);
        $httpCode = (int)$this->curl->getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $curlErrorNumber = $this->curl->errno($curlHandle);
        $curlError = $this->curl->error($curlHandle);

        $this->curl->close($curlHandle);

        return FF::getInstance('Core\Server\Response',
            $responseText,
            $httpCode,
            $curlErrorNumber,
            $curlError
        );
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
