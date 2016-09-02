<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

/**
 * Base class for all adapters which support the personalisation.
 */
abstract class PersonalisedResponse extends ConfigurableResponse
{
    /**
     * @var string
     */
    protected $sid = false;
    
    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *        implement FACTFinder\Util\LoggerInterface.
     * @param \FACTFinder\Core\ConfigurationInterface $configuration
     *        Configuration object to use.
     * @param \FACTFinder\Core\Server\Request $request The request object from
     *        which to obtain the server data.
     * @param \FACTFinder\Core\Client\UrlBuilder $urlBuilder
     *        Client URL builder object to use.
     * @param \FACTFinder\Core\encodingConverter $encodingConverter
     *        Encoding converter object to use
     */
    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder, $encodingConverter);
    }
    
    /**
     * Set the session id for personalization.
     *
     * @param string $sessionId session id
     */
    public function setSid($sessionId)
    {
        if (strcmp($sessionId, $this->sid) !== 0) {
            $this->sid = $sessionId;
            $this->parameters['sid'] = $this->sid;
            $this->upToDate = false;
        }
    }
 }
