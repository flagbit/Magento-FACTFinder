<?php

/**
 * similar records adapter using the xml interface
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: SimilarRecordsAdapter.php 42804 2012-01-20 10:46:43Z mb $
 * @package   FACTFinder\Xml66
 */
class FACTFinder_Xml66_SimilarRecordsAdapter extends FACTFinder_Abstract_SimilarRecordsAdapter
{
    /**
     * {@inheritdoc}
     *
     * @return void
     **/
    public function init() {
        $this->log->info("Initializing new similar records adapter.");
        $this->getDataProvider()->setParam('format', 'xml');
        $this->getDataProvider()->setType('SimilarRecords.ff');
    }

    /**
     * try to parse data as xml
     *
     * @throws Exception of data is no valid XML
     * @return SimpleXMLElement
     */
    protected function getData() {
        libxml_use_internal_errors(true);
        return new SimpleXMLElement(parent::getData()); //throws exception on error
    }
    
    protected function reloadData() {
        libxml_use_internal_errors(true);
        return new SimpleXMLElement(parent::reloadData()); //throws exception on error
    }
    
    /**
     * {@inheritdoc}
     *
     * @param string id of the product which should be used to get similar attributes
     * @return array $similarAttributes of strings (field names as keys)
     **/
    protected function createSimilarAttributes() {
        $similarAttributes = array();
        $xmlSimilarAttributes = $this->reloadData()->similarAttributes;
        if (!empty($xmlSimilarAttributes)) {
            foreach($xmlSimilarAttributes->attribute AS $currentAttribute){
                $currentAttribute = (string) $currentAttribute->attributes()->name;
                $similarAttributes[$currentAttribute] = (string) $currentAttribute;
            }
        }
        return $similarAttributes;
    }

    /**
     * {@inheritdoc}
     *
     * @param string id of the product which should be used to get similar records
     * @return array $similarRecords list of FACTFinder_Record items
     **/
    protected function createSimilarRecords() {
        $similarRecords = array();
        $xmlSimilarRecords = $this->reloadData()->similarRecords;
        if (!empty($xmlSimilarRecords)) {
            $encodingHandler = $this->getEncodingHandler();
            
            $positionCounter = 1;
            foreach($xmlSimilarRecords->record AS $currentRecord) {
                // get current position
                $position = $positionCounter;
                $positionCounter++;

                if ($this->idsOnly) {
                    $similarRecords[] = FF::getInstance('record', $currentRecord->attributes()->id);
                    continue;
                }
                
                // fetch record values
                $fieldValues = array();
                foreach($currentRecord->field AS $current_field){
                    $currentFieldname = (string) $current_field->attributes()->name;
                    $fieldValues[$currentFieldname] = (string) $current_field;
                }

                // get original position
                if (isset($fieldValues['__ORIG_POSITION__'])) {
                    $origPosition = $fieldValues['__ORIG_POSITION__'];
                    unset($fieldValues['__ORIG_POSITION__']);
                } else {
                    $origPosition = $position;
                }

                $similarRecords[] = FF::getInstance('record',
                    $currentRecord->attributes()->id,
                    floatval($currentRecord->attributes()->relevancy),
                    $position,
                    $origPosition,
                    $encodingHandler->encodeServerContentForPage($fieldValues)
                );
            }
        }
        return $similarRecords;
    }
}
