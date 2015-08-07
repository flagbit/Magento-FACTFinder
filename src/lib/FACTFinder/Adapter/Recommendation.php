<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class Recommendation extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Data\Result
     */
    private $recommendations;

    /**
     * @var bool
     */
    private $recommendationsUpToDate = false;

    /**
     * @var bool
     */
    private $idsOnly = false;


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

        $this->request->setAction('Recommender.ff');
        $this->parameters['do'] = 'getRecommendation';
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }


    /**
     * Set the maximum amount of recommendations to be fetched.
     *
     * @param int $recordCount The number of records to be fetched. Something
     *        else than a positive integer is passed, the record count will be
     *        unlimited (or determined by FACT-Finder).
     */
    public function setRecordCount($recordCount)
    {
        $parameters = $this->request->getParameters();
        if (is_numeric($recordCount)
            && (int)$recordCount == (float)$recordCount // Is integer?
            && $recordCount > 0
        ) {
            $parameters['maxResults'] = $recordCount;
        }
        else
        {
            unset($parameters['maxResults']);
        }
        // Make sure that the recommendations are fetched again. In theory,
        // we only have to do this when recordCount increases.
        $this->recommendationsUpToDate = false;
    }

    /**
     * Set one or multiple product IDs to base recommendation on, overwriting
     * any IDs previously set.
     *
     * @param string|string[] $productIDs One or more product IDs.
     */
    public function setProductIDs($productIDs)
    {
        $parameters = $this->request->getParameters();
        $parameters['id'] = $productIDs;
        $this->recommendationsUpToDate = false;
    }

    /**
     * Add one or multiple product IDs to base recommendation on, in addition to
     * any IDs previously set.
     *
     * @param string|string[] $productIDs One or more product IDs.
     */
    public function addProductIDs($productIDs)
    {
        $parameters = $this->request->getParameters();
        $parameters->add('id', $productIDs);
        $this->recommendationsUpToDate = false;
    }

    /**
     * Set this to true to only retrieve the IDs of recommended products instead
     * of full Record objects.
     * @param $idsOnly bool
     */
    public function setIdsOnly($idsOnly)
    {
        // Reset the recommendations, if more detail is wanted than before
        if($this->idsOnly && !$idsOnly)
            $this->recommendationsUpToDate = false;

        $this->idsOnly = $idsOnly;
        $parameters = $this->request->getParameters();
        $parameters['idsOnly'] = $idsOnly ? 'true' : 'false';
    }

    /**
     * Returns recommendations for IDs previously specified. If no IDs have been
     * set, there will be a warning raised and an empty result will be returned.
     *
     * @return \FACTFinder\Data\Result
     */
    public function getRecommendations()
    {
        if (is_null($this->recommendations)
            || !$this->recommendationsUpToDate
        ) {
            $this->recommendations = $this->createRecommendations();
            $this->recommendationsUpToDate = true;
        }

        return $this->recommendations;
    }

    private function createRecommendations()
    {
        $records = array();

        $parameters = $this->request->getParameters();
        if (!isset($parameters['id']))
        {
            $this->log->warn('Recommendations cannot be loaded without a product ID. '
                           . 'Use setProductIDs() or addProductIDs() first.');
        }
        else
        {
            $recommenderData = $this->getResponseContent();
            if (isset($recommenderData['resultRecords']))
            {
                $recommenderData = $recommenderData['resultRecords'];
            }
            $position = 1;
            foreach($recommenderData as $recordData)
            {
                if ($this->idsOnly)
                    $records[] = $this->createSparseRecord($recordData);
                else
                    $records[] = $this->createRecord($recordData, $position++);
            }
        }

        return FF::getInstance(
            'Data\Result',
            $records,
            null,
            count($records)
        );
    }

    private function createSparseRecord($recordData)
    {
        return FF::getInstance(
            'Data\Record',
            (string)$recordData['id']
        );
    }

    private function createRecord($recordData, $position)
    {
        return FF::getInstance(
            'Data\Record',
            (string)$recordData['id'],
            $recordData['record'],
            100.0,
            $position
        );
    }
	
    /**
     * Get the recommendations from FACT-Finder as the string returned by the
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
    public function getRawRecommendations($format = null, $callback = null)
    {
        $this->usePassthroughResponseContentProcessor();

        if (!is_null($format))
            $this->parameters['format'] = $format;
        if (!is_null($callback))
            $this->parameters['callback'] = $callback;

        return $this->getResponseContent();
    }
}
