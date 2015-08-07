<?php
namespace FACTFinder\Core\Client;

use FACTFinder\Loader as FF;

/**
 * Generates URLs to be used in requests to the client.
 */
class UrlBuilder
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\ParametersConverter
     */
    private $parametersConverter;

    /**
     * @var FACTFinder\Core\Client\RequestParser
     */
    private $requestParser;

    /**
     * @var FACTFinder\Core\AbstractEncodingConverter
     */
    private $encodingConverter;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Client\RequestParser $requestParser,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->parametersConverter = FF::getInstance(
            'Core\ParametersConverter',
            $loggerClass,
            $configuration
        );
        $this->requestParser = $requestParser;
        $this->encodingConverter = $encodingConverter;
    }

    /**
     * Generates a link to be used on the page that leads to the client from a
     * set of server parameters. Note that the link will still be UTF-8 encoded.
     * If the page uses a different encoding, conversion to that encoding has to
     * be done when actually rendering the string to the page.
     *
     * TODO: Should the signature be more similar to that of \Server\UrlBuilder?
     *
     * @param FACTFinder\Util\Parameters $parameters The server parameters that
     *        should be retrieved when the link is followed.
     * @param string $target An optional request target. If omitted, the target
     *        of the current request will be used. For instance, this parameter
     *        can be used if a product detail page needs a different target.
     *
     * @return string
     */
    public function generateUrl($parameters, $target = null)
    {
        $parameters = $this->parametersConverter
                           ->convertServerToClientParameters($parameters);

        $parameters = $this->encodingConverter != null ? $this->encodingConverter->encodeClientUrlData($parameters) : $parameters;

        if (!is_string($target))
            $target = $this->requestParser->getRequestTarget();

        $url = $target . '?' . $parameters->toPhpQueryString();
        return $url;
    }
}
