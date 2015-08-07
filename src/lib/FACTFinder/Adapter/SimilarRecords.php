<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class SimilarRecords extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var mixed[]
     * @see getSimilarAttributes()
     */
    private $similarAttributes;

    /**
     * @var FACTFinder\Data\Result
     */
    private $similarRecords;

    /**
     * @var bool
     */
    private $attributesUpToDate = false;
    private $recordsUpToDate = false;

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

        $this->request->setAction('SimilarRecords.ff');
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }


    /**
     * Set the maximum amount of similar records to be fetched.
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
            $parameters['maxRecordCount'] = $recordCount;
        }
        else
        {
            unset($parameters['maxRecordCount']);
        }
        // Make sure that the records are fetched again. In principle, we only
        // have to do this when recordCount increases.
        $this->recordsUpToDate = false;
    }

    /**
     * Set one product IDs to get similar records for.
     *
     * @param string $productID
     */
    public function setProductID($productID)
    {
        $parameters = $this->request->getParameters();
        $parameters['id'] = $productID;
        $this->attributesUpToDate = false;
        $this->recordsUpToDate = false;
    }

    /**
     * Set this to true to only retrieve the IDs of similar products instead
     * of full Record objects.
     * @param $idsOnly bool
     */
    public function setIDsOnly($idsOnly)
    {
        // Reset the similar records, if more detail is wanted than before
        if($this->idsOnly && !$idsOnly)
            $this->recordsUpToDate = false;

        $this->idsOnly = $idsOnly;
        $parameters = $this->request->getParameters();
        $parameters['idsOnly'] = $idsOnly ? 'true' : 'false';
    }

    /**
     * Returns the attributes based on which similar records are determined as
     * well as the source product's values of those attributes. If no ID has
     * been set, there will be a warning raised and an empty array will be
     * returned.
     *
     * @return mixed[] Attribute names as keys and attribute values as values.
     */
    public function getSimilarAttributes()
    {
        if (is_null($this->similarAttributes)
            || !$this->attributesUpToDate
        ) {
            $this->similarAttributes = $this->createSimilarAttributes();
            $this->attributesUpToDate = true;
        }

        return $this->similarAttributes;
    }

    private function createSimilarAttributes()
    {
        $attributes = array();

        $parameters = $this->request->getParameters();
        if (!isset($parameters['id']))
        {
            $this->log->warn('Similar attributes cannot be loaded without a product ID. '
                           . 'Use setProductID() first.');
        }
        else
        {
            $jsonData = $this->getResponseContent();
            foreach($jsonData['attributes'] as $attributeData)
            {
                $attributes[$attributeData['name']] = $attributeData['value'];
            }
        }

        return $attributes;
    }

    /**
     * Returns similar records for the ID previously specified. If no ID has
     * been set, there will be a warning raised and an empty result will be
     * returned.
     *
     * @return \FACTFinder\Data\Result
     */
    public function getSimilarRecords()
    {
        if (is_null($this->similarRecords)
            || !$this->recordsUpToDate
        ) {
            $this->similarRecords = $this->createSimilarRecords();
            $this->recordsUpToDate = true;
        }

        return $this->similarRecords;
    }

    private function createSimilarRecords()
    {
        $records = array();

        $parameters = $this->request->getParameters();
        if (!isset($parameters['id']))
        {
            $this->log->warn('Similar records cannot be loaded without a product ID. '
                           . 'Use setProductID() first.');
        }
        else
        {
            $position = 1;
            $jsonData = $this->getResponseContent();
            foreach($jsonData['records'] as $recordData)
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
}
