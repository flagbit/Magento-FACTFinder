<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class Import extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

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

        // Don't set request action yet, because it depends on the kind of
        // import to be done.
        $this->parameters['format'] = 'xml';

        $this->request->setConnectTimeout($configuration->getImportConnectTimeout());
        $this->request->setTimeout($configuration->getImportTimeout());

        $this->useXmlResponseContentProcessor();
    }

    /**
     * Trigger a search data import.
     *
     * @param bool $download If true, update import files prior to import.
     */
    public function triggerDataImport($download = false)
    {
        $this->request->setAction('Import.ff');

        $this->parameters['download'] = $download ? 'true' : 'false';

        // TODO: Parse the response XML into some nice domain object.
        return $this->getResponseContent();
    }

    /**
     * Trigger a suggest data import.
     *
     * @param bool $download If true, update import files prior to import.
     */
    public function triggerSuggestImport($download = false)
    {
        $this->request->setAction('Import.ff');

        $this->parameters['download'] = $download ? 'true' : 'false';
        $this->parameters['type'] = 'suggest';

        $report = $this->getResponseContent();

        // Clean up for next import
        unset($this->parameters['type']);

        // TODO: Parse the response XML into some nice domain object.
        return $report;
    }

    /**
     * Trigger a recommendation data import.
     *
     * @param bool $download If true, update import files prior to import.
     */
    public function triggerRecommendationImport($download = false)
    {
        $this->request->setAction('Recommender.ff');

        $this->parameters['download'] = $download ? 'true' : 'false';
        $this->parameters['do'] = 'importData';

        $report = $this->getResponseContent();

        // Clean up for next import
        unset($this->parameters['do']);

        // TODO: Parse the response XML into some nice domain object.
        return $report;
    }
}
