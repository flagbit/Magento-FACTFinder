<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

/**
 * Base class for all adapters which support the response of records with ids only.
 */
abstract class ConfigurableResponse extends AbstractAdapter
{
    /**
     * @var bool
     */
    protected $idsOnly = false;
    
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
     * Set this to true to only retrieve the IDs of products instead
     * of full Record objects.
     * @param $idsOnly bool
     */
    public function setIdsOnly($idsOnly)
    {
        if($this->idsOnly && !$idsOnly)
            $this->upToDate = false;

        $this->idsOnly = $idsOnly;
        $parameters = $this->request->getParameters();
        $parameters['idsOnly'] = $idsOnly ? 'true' : 'false';
    }
 }
