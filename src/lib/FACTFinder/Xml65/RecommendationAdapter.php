<?php

/**
 * adapter for the factfinder recommendation engine, working with the XML interface of FF6.5
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_RecommendationAdapter extends FACTFinder_Abstract_RecommendationAdapter
{

    protected $xmlData = null;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        $this->getDataProvider()->setParam('do', 'getRecommendation');
        $this->getDataProvider()->setParam('format', 'xml');
        $this->getDataProvider()->setType('Recommender.ff');
    }

    /**
     * try to parse data as xml
     *
     * @throws Exception of data is no valid XML
     * @return SimpleXMLElement
     */
    protected function getData()
    {
        if ($this->xmlData == null) {
            libxml_use_internal_errors(true);
            $data = parent::getData();
            $this->xmlData = new SimpleXMLElement($data); //throws exception on error
        }
        return $this->xmlData;
    }

    /**
     * creates the recommendation-records.
     * each record has a similarity of 100.0%, because the similarity is not known. the position is just
     * the position at the recommendations result starting from 0 - there is no "original position" at
     * these records.
     *
     * @param string id of the product which should be used to get some recommendations
     * @return array of FACTFinder_Record objects
     *
     */
    protected function createRecommendations($id) {
        $this->getDataProvider()->setParam('id', $id);
        $xmlResult = $this->getData(); //throws exception on error

        $records = array();
        if (!empty($xmlResult->results)) {
            $count = (int) $xmlResult->results->attributes()->count;
            $encodingHandler = $this->getEncodingHandler();

            //load result
            foreach($xmlResult->results->record AS $xmlRecord){

                // fetch record values
                $fieldValues = array();
                foreach($xmlRecord->field AS $xmlField){
                    $fieldName = (string) $xmlField->attributes()->name;
                    $fieldValues[$fieldName] = (string) $xmlField;
                }

                $record = FF::getInstance('record', $xmlRecord->attributes()->id, 100.0, $xmlRecord->attributes()->nr);
                $record->setValues($fieldValues);
                $records[] = $record;
            }
        }
        return FF::getInstance('result', $records, $count);
    }
}
