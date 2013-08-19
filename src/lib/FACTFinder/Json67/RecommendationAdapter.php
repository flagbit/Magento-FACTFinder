<?php
/**
 * adapter for the factfinder recommendation engine, working with the JSON interface of FF6.7
 */
class FACTFinder_Json67_RecommendationAdapter extends FACTFinder_Json66_RecommendationAdapter
{
    private $jsonData;
    
    /**
     * @return void
     **/
    public function init()
    {
		parent::init();
		$this->log->info("Initializing new recommendation adapter.");
        $this->getDataProvider()->setParam('do', 'getRecommendation');
		$this->getDataProvider()->setParam('format', 'json');
        $this->getDataProvider()->setType('Recommender.ff');
    }
    
    /**
     * try to parse data as json
     *
     * @throws Exception of data is no valid JSON
     * @return stdClass
     */
    protected function getData()
    {
        if($this->jsonData === null || !$this->recommendationUpToDate)
        {
            $this->jsonData = json_decode(parent::getData(), true); // the second parameter turns JSON-objects into associative arrays which makes extracting the record fields easier
            if ($this->jsonData === null)
                throw new InvalidArgumentException("json_decode() raised error ".json_last_error());
            $this->recommendationUpToDate = true;
        }
        return $this->jsonData;
    }
    
    protected function createRecommendations() {        
        $records = array();
        
        $position = 0;
        foreach($this->getData() as $recordData)
            if ($this->idsOnly)
                $records[] = $this->createSparseRecordFrom($recordData);
            else
                $records[] = $this->createRecordFrom($recordData, $position++);
        
        return FF::getInstance('result', $records, count($records));    
	}
    
    protected function createSparseRecordFrom($recordData)
    {
        return FF::getInstance('record', strval($recordData["id"]));
    }
    
    protected function createRecordFrom($recordData, $position)
    {
        return FF::getInstance('record', 
            strval($recordData["id"]),
            100.0, 
            $position,
            $position,
            $recordData["record"]);
    }
    
	/**
	 * Set ids of products to base recommendation on
	 * 
	 * @param array $productIds list of integers
	 **/
	public function setProductIds($productIds) {
		$this->productIds = $productIds;
		$this->getDataProvider()->setArrayParam('id', $productIds);
		$this->recommendationUpToDate = false;
	}

	/**
	 * Adds an id to the list of products to base recommendation on
	 * 
	 * @param int $productId
	 **/
	public function addProductId($productId) {
		$this->productIds[] = $productId;
		$this->getDataProvider()->setArrayParam('id', $this->productIds);
		$this->recommendationUpToDate = false;
	}
}
