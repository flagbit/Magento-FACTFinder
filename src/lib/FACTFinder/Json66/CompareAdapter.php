<?php
class FACTFinder_Json66_CompareAdapter extends FACTFinder_Default_CompareAdapter
{
    /**
     * @return void
     **/
    public function init() {
        $this->log->info("Initializing new compare adapter.");
        $this->getDataProvider()->setParam('format', 'json');
        $this->getDataProvider()->setType('Compare.ff');
    }

    /**
     * try to parse data as json
     *
     * @throws Exception of data is no valid JSON
     * @return stdClass
     */
    protected function getData()
    {
        $jsonData = json_decode(parent::getData(), true);
        if ($jsonData === null)
            throw new InvalidArgumentException("json_decode() raised error ".json_last_error());
        return $jsonData;
    }

    /**
     * @return array $comparableAttributes of strings (field names as keys, hasDifferences as values)
     **/
    protected function createComparableAttributes() {
        $comparableAttributes = array();
        $jsonData = $this->getData();
        foreach($jsonData['attributes'] as $attributeData){
            $name = $attributeData['attributeName'];
            $comparableAttributes[$name] = $attributeData['different'];
        }
        return $comparableAttributes;
    }

    /**
     * @return array $comparedRecords list of FACTFinder_Record items
     **/
    protected function createComparedRecords() {
        $comparedRecords = array();
        $jsonData = $this->getData();
        if (!empty($jsonData['records'])) {
            $encodingHandler = $this->getEncodingHandler();
            
            if ($this->idsOnly && !$this->attributesUpToDate) {
                $this->createComparableAttributes();
                $this->attributesUpToDate = true;
            }
            
            $positionCounter = 1;
            foreach ($jsonData['records'] AS $recordData){
                // get current position
                $position = $positionCounter;
                $positionCounter++;

                $comparedRecords[] = FF::getInstance('record',
                    $recordData['id'],
                    100,
                    $position,
                    $position,
                    $this->getEncodingHandler()->encodeServerContentForPage($recordData['record'])
                );
            }
        }
        return $comparedRecords;
    }
}
