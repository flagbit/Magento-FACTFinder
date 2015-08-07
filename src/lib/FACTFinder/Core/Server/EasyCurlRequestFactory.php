<?php
namespace FACTFinder\Core\Server;

use \FACTFinder\Loader as FF;

/**
 * This implementation backs the Request with an EasyCurlDataProvider.
 */
class EasyCurlRequestFactory implements RequestFactoryInterface
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;
    private $loggerClass;

    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var EasyCurlDataProvider
     */
    private $dataProvider;

    /**
     * @var \FACTFinder\Util\Parameters
     */
    private $requestParameters;

    /**
     * @param string $loggerClass
     * @param \FACTFinder\Core\ConfigurationInterface $configuration
     * @param \FACTFinder\Util\Parameters $requestParameters
     * @param \FACTFinder\Util\CurlInterface $curl Optional. If omitted, an
     *        instance of \FACTFinder\Util\Curl will be used.
     */
    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Util\Parameters $requestParameters,
        \FACTFinder\Util\CurlInterface $curl = null
    ) {
        $this->loggerClass = $loggerClass;
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;

        $urlBuilder = FF::getInstance('Core\Server\UrlBuilder',
            $loggerClass,
            $configuration
        );
        $this->dataProvider = FF::getInstance('Core\Server\EasyCurlDataProvider',
            $loggerClass,
            $configuration,
            is_null($curl) ? FF::getInstance('Util\Curl') : $curl,
            $urlBuilder
        );

        $this->requestParameters = $requestParameters;
    }

    /**
     * Returns a request object all wired up and ready for use.
     * @return Request
     */
    public function getRequest()
    {
        $connectionData = FF::getInstance(
            'Core\Server\ConnectionData',
            clone $this->requestParameters
        );
        return FF::getInstance('Core\Server\Request',
            $this->loggerClass,
            $connectionData,
            $this->dataProvider
        );
    }
}
