<?php

/**
 * adapter for the factfinder "similar records" data
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: SimilarRecordsAdapter.php 42804 2012-01-20 10:46:43Z mb $
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_SimilarRecordsAdapter extends FACTFinder_Abstract_Adapter
{
    protected $productId;
    private $similarAttributes;
    private $similarRecords;
    protected $attributesUpToDate = false;
    protected $recordsUpToDate = false;
    
    protected $idsOnly = false;
    
    /*
     * Option for XML query. 0 means "no maximum".
     */
    private $maxRecordCount = 0;
    
    /**
     * Set id of product to get similar records for
     * 
     * @param int $productId
     **/
    public function setProductId($productId) {
        $this->productId = $productId;
        $this->getDataProvider()->setParam('id', $productId);
        $this->attributesUpToDate = false;
        $this->recordsUpToDate = false;
    }
    
    /*
     * @return int $maxRecordCount
     */
    public function getMaxRecordCount() {
        return $this->maxRecordCount;
    }
    
    /*
     * @param int $count positive integer (negative will be treated as 0)
     */
    public function setMaxRecordCount($count) {
        $this->maxRecordCount = $count < 1 ? 0 : $count;
        if($count > 0)    $this->getDataProvider()->setParam('maxRecordCount', $count);
        else $this->getDataProvder()->unsetParam('maxRecordCount');
    }
    
    public function setIdsOnly($idsOnly) {
        // Reset the similar records, if more detail is wanted than before
        if($this->idsOnly && !$idsOnly) $this->recordsUpToDate = false;
        $this->idsOnly = $idsOnly;
        if($idsOnly)
            $this->getDataProvider()->setParam('idsOnly','true');
        else
            $this->getDataProvider()->unsetParam('idsOnly');
    }
    
    /**
     * returns similar attributes for previously specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array
     
     * @return array $similarAttributes of strings (field names as keys)
     */
    public function getSimilarAttributes() {
        if (empty($this->productId)) {
            $requestParams = $this->getParamsParser()->getRequestParams();
            if (isset($requestParams['id'])) {
                $this->productId = $requestParams['id'];
            }
            if (empty($this->productId)) {
                trigger_error('recommendations can not be loaded without id. could not load id from request', E_USER_WARNING);
                return array();
            }
        }
        if (!$this->attributesUpToDate || !isset($this->similarAttributes) || $this->similarAttributes == null) {
            $this->similarAttributes = $this->createSimilarAttributes();
            $this->attributesUpToDate = true;
        }
        return $this->similarAttributes;
    }
    
    /**
     * returns similar records for specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array.
     *
     * @param string $id (optional; if not set try to fetch from request param)
     * @return array $similarRecords list of FACTFinder_Record objects
     */
    public function getSimilarRecords() {
        if (empty($this->productId)) {
            $requestParams = $this->getParamsParser()->getRequestParams();
            if (isset($requestParams['id'])) {
                $this->productId = $requestParams['id'];
            }
            if (empty($this->productId)) {
                trigger_error('similar records can not be loaded without id. could not load id from request', E_USER_WARNING);
                return array();
            }
        }
        if (!$this->recordsUpToDate || !isset($this->similarRecords) || $this->similarRecords == null) {
            $this->similarRecords = $this->createSimilarRecords();
            $this->recordsUpToDate = true;
        }
        return $this->similarRecords;
    }
    
    /**
     * @return array $similarAttributes of strings (field names as keys)
     */
    abstract protected function createSimilarAttributes();
    
    /**
     * @return array $similarRecords list of FACTFinder_Record objects
     */
    abstract protected function createSimilarRecords();
}