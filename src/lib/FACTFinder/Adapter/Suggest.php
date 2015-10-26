<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

/**
 * TODO: Are there any other FF 6.8 features left which we are not making use of
 *       yet? If so: change that.
 */
class Suggest extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Data\SuggestQuery[]
     */
    private $suggestions;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder, $encodingConverter);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('Suggest.ff');

        $this->request->setConnectTimeout($configuration->getSuggestConnectTimeout());
        $this->request->setTimeout($configuration->getSuggestTimeout());
    }

    /**
     * Get the suggestions from FACT-Finder as an array of SuggestQuery's.
     *
     * @return FACTFinder\Data\SuggestQuery[]
     */
    public function getSuggestions()
    {
        if (is_null($this->suggestions))
            $this->suggestions = $this->createSuggestions();

        return $this->suggestions;
    }

    private function createSuggestions()
    {
        $suggestions = array();

        $this->useJsonResponseContentProcessor();

        if (isset($this->parameters['format']))
            $oldFormat = $this->parameters['format'];

        $this->parameters['format'] = 'json';
        $suggestData = $this->getResponseContent();
        if (!empty($suggestData))
        {
            if (isset($suggestData['suggestions']))
            {
                $suggestData = $suggestData['suggestions'];
            }
            
            foreach ($suggestData as $suggestQueryData)
            {
                $suggestLink = $this->convertServerQueryToClientUrl(
                    $suggestQueryData['searchParams']
                );

                $suggestAttributes = null;
                if (isset($suggestQueryData['attributes'])
                    && is_array($suggestQueryData['attributes'])
                ) {
                    $suggestAttributes = $suggestQueryData['attributes'];
                } else {
                    $suggestAttributes = array();
                }

                $suggestions[] = FF::getInstance(
                    'Data\SuggestQuery',
                    $suggestQueryData['name'],
                    $suggestLink,
                    $suggestQueryData['hitCount'],
                    $suggestQueryData['type'],
                    $suggestQueryData['image'],
                    isset($suggestQueryData['refKey']) ? $suggestQueryData['refKey'] : '',
                    $suggestAttributes
                );
            }
        }

        if (isset($oldFormat))
            $this->parameters['format'] = $oldFormat;

        return $suggestions;
    }

    /**
     * Get the suggestions from FACT-Finder as the string returned by the
     * server.
     *
     * @param string $format Optional. Either 'json' or 'jsonp'. Use to
     *                       overwrite the 'format' parameter.
     * @param string $callback Optional name to overwrite the 'callback'
     *                         parameter, which determines the name of the
     *                         callback the response is wrapped in.
     *
     * @return string
     */
    public function getRawSuggestions($format = null, $callback = null)
    {
        $this->usePassthroughResponseContentProcessor();

        if (!is_null($format))
            $this->parameters['format'] = $format;
        if (!is_null($callback))
            $this->parameters['callback'] = $callback;

        return $this->getResponseContent();
    }
}
