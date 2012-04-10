<?php

/**
 * product comparison adapter using the xml interface
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: SimilarRecordsAdapter.php 42955 2012-01-25 17:04:47Z mb $
 * @package   FACTFinder\Xml66
 */
class FACTFinder_Xml66_CompareAdapter extends FACTFinder_Abstract_CompareAdapter
{
    /**
     * {@inheritdoc}
     *
     * @return void
     **/
    public function init() {
        $this->log->info("Initializing new compare adapter.");
        $this->getDataProvider()->setParam('format', 'xml');
        $this->getDataProvider()->setType('Compare.ff');
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

    /**
     * {@inheritdoc}
     *
     * @return array $comparableAttributes of strings (field names as keys, hasDifferences as values)
     **/
    protected function createComparableAttributes() {
        $comparableAttributes = array();
        $xmlComparableAttributes = $this->getData()->attributes;
        if (!empty($xmlComparableAttributes)) {
            foreach($xmlComparableAttributes->attribute AS $currentAttribute){
                $name = (string) $currentAttribute->attributes()->name;
                $comparableAttributes[$name] = ((string) $currentAttribute->attributes()->hasDifferences == "true") ? true : false;
            }
        }
        return $comparableAttributes;
    }

    /**
     * {@inheritdoc}
     *
     * @return array $comparedRecords list of FACTFinder_Record items
     **/
    protected function createComparedRecords() {
        $comparedRecords = array();
        $xmlComparedRecords = $this->getData()->results;
        if (!empty($xmlComparedRecords)) {
            $encodingHandler = $this->getEncodingHandler();
            
            if($this->idsOnly && !$this->attributesUpToDate) {
                $this->createComparableAttributes();
                $this->attributesUpToDate = true;
            }
            
            $positionCounter = 1;
            foreach($xmlComparedRecords->record AS $currentRecord) {
                // get current position
                $position = $positionCounter;
                $positionCounter++;

                // fetch record values
                $fieldValues = array();
                foreach($currentRecord->field AS $current_field){
                    $currentFieldname = (string) $current_field->attributes()->name;
                    if(!$this->idsOnly || array_key_exists($currentFieldname, $this->comparableAttributes)) {
                        $fieldValues[$currentFieldname] = (string) $current_field;
                    }
                }

                // get original position
                if (isset($fieldValues['__ORIG_POSITION__'])) {
                    $origPosition = $fieldValues['__ORIG_POSITION__'];
                    unset($fieldValues['__ORIG_POSITION__']);
                } else {
                    $origPosition = $position;
                }

                $comparedRecords[] = FF::getInstance('record',
                    $currentRecord->attributes()->id,
                    floatval($currentRecord->attributes()->relevancy),
                    $position,
                    $origPosition,
                    $encodingHandler->encodeServerContentForPage($fieldValues)
                );
            }
        }
        return $comparedRecords;
    }
}
