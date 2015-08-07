<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

/**
 * Base class for all adapters. An adapter is a class that configures a request
 * to some FACT-Finder action and transforms the result into useful domain
 * objects (usually objects of classes from the \Data namespace).
 * The adapter classes could conceivably be placed in the \Core\Server
 * namespace, but that would potentially discourage fiddling with and extending
 * these classes. The adapters are main components of the external API of this
 * library. Most other classes are just used to make the adapters work.
 */
abstract class AbstractAdapter
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
     * @var \FACTFinder\Core\Server\Request
     */
    protected $request;

    /**
     * @var FACTFinder\Util\Parameters
     */
    protected $parameters;

    /**
     * @var \FACTFinder\Core\Client\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \FACTFinder\Util\ContentProcessorInterface
     */
    private $responseContentProcessor;

    /**
     * @var \FACTFinder\Core\Server\Response
     */
    private $lastResponse = null;

    /**
     * @var object The processed response content.
     */
    private $responseContent = null;

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
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;
        $this->request = $request;
        $this->parameters = $request->getParameters();
        $this->urlBuilder = $urlBuilder;
        $this->encodingConverter = $encodingConverter;

        $this->usePassthroughResponseContentProcessor();
    }

    protected function usePassthroughResponseContentProcessor()
    {
        $this->responseContentProcessor = function($string) {
            return $string;
        };
    }

    protected function useJsonResponseContentProcessor()
    {
        $this->responseContentProcessor = function($string) {

            // The second parameter turns objects into associative arrays.
            // stdClass objects don't really have any advantages over plain
            // arrays but miss out on some of the built-in array functions.
            $jsonData = json_decode($string, true);

            if (is_null($jsonData))
                throw new \InvalidArgumentException(
                    "json_decode() raised an error: ".json_last_error()
                );

            return $jsonData;
        };
    }

    protected function useXmlResponseContentProcessor()
    {
        $this->responseContentProcessor = function($string) {
            libxml_use_internal_errors(true);
            // The constructor throws an exception on error
            return new \SimpleXMLElement($string);
        };
    }

    /**
     * Pass in a function to process the response content. This method is not
     * used within the library, but may be convenient when writing custom
     * adapters.
     *
     * @param object $callable A function (or invokable object) that processes
     *        a single string parameter.
     *
     * @throws InvalidArgumentException if $callable is not callable.
     */
    protected function useResponseContentProcessor($callable)
    {
        // Check shamelessly stolen from Pimple.php
        if (!method_exists($callable, '__invoke'))
            throw new \InvalidArgumentException('Content processor is neither a Closure or invokable object.');

        $this->responseContentProcessor = $callable;

        // Invalidate processed response content
        $this->responseContent = null;
    }

    protected function getResponseContent()
    {
        $response = $this->request->getResponse();

        // Only reprocess the response content, if the response is new.
        if (is_null($this->responseContent)
            || $response !== $this->lastResponse
        ) {
            $content = $response->getContent();

            // PHP does not (yet?) support $this->method($args) for callable
            // properties
          
            $this->responseContent =  $this->responseContentProcessor->__invoke($content);
            if ($this->encodingConverter != null)
            {
                $this->responseContent = $this->encodingConverter->encodeContentForPage($this->responseContent);
            }
            $this->lastResponse = $response;
        }

        return $this->responseContent;
    }

    protected function convertServerQueryToClientUrl($query)
    {
        $parameters = FF::getInstance(
            'Util\Parameters',
            $query,
            true
        );

        return $this->urlBuilder->generateUrl($parameters);
    }
 }
