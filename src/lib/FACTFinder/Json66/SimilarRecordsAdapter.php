<?php
class FACTFinder_Json66_SimilarRecordsAdapter extends FACTFinder_Default_SimilarRecordsAdapter
{
    /**
     * @return void
     **/
    public function init() {
		$this->log->info("Initializing new similar records adapter.");
		$this->getDataProvider()->setParam('format', 'json');
        $this->getDataProvider()->setType('SimilarRecords.ff');
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
    
    protected function reloadData()
    {
        $jsonData = json_decode(parent::reloadData(), true);
        if ($jsonData === null)
            throw new InvalidArgumentException("json_decode() raised error ".json_last_error());
        return $jsonData;
    }
	
	/**
     * @param string id of the product which should be used to get similar attributes
     * @return array $similarAttributes of value strings (field names as keys)
     **/
    protected function createSimilarAttributes() {
        $similarAttributes = array();
        $jsonData = $this->reloadData();
        foreach($jsonData['attributes'] as $attributeData){
            $similarAttributes[$attributeData['name']] = $attributeData['value'];
        }
        return $similarAttributes;
    }

    /**
     * @param string id of the product which should be used to get similar records
     * @return array $similarRecords list of FACTFinder_Record items
     **/
    protected function createSimilarRecords() {
        $similarRecords = array();
        $jsonData = $this->getData();
        if (!empty($jsonData['records'])) {
            $encodingHandler = $this->getEncodingHandler();
			
			$positionCounter = 1;
            foreach($jsonData['records'] as $recordData) {
                // get current position
                $position = $positionCounter;
                $positionCounter++;

                $similarRecords[] = FF::getInstance('record',
                    $recordData['id'],
                    100,
                    $position,
                    $position,
                    $this->getEncodingHandler()->encodeServerContentForPage($recordData['record'])
                );
            }
        }
        return $similarRecords;
    }
}
