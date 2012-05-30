<?php

/**
 * adapter for the factfinder recommendation engine
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_RecommendationAdapter extends FACTFinder_Abstract_Adapter
{
    protected $productIds = array();
    protected $recommendation;
    protected $recommendationUpToDate = false;
    
    protected $idsOnly = false;
    /*
     * Option for XML query. 0 means "no maximum".
     */
    protected $maxResults = 0;
    
    /*
     * @return int $maxResults
     */
    public function getMaxResults() {
        return $this->maxResults;
    }
    
    /*
     * @param int $count positive integer (negative will be treated as 0)
     */
    public function setMaxResults($count) {
        $this->maxResults = $count < 1 ? 0 : $count;
        if($count > 0)    $this->getDataProvider()->setParam('maxResults', $count);
        else $this->getDataProvder()->unsetParam('maxResults');
    }
    
    /**
     * Set id of product to base recommendation on
     * 
     * @param int $productId
     **/
    public function setProductId($productId) {
        $this->productIds = array($productId);
        $this->getDataProvider()->setParam('id', $productId);
        $this->recommendationUpToDate = false;
    }
    
    public function setIdsOnly($idsOnly) {
        // Reset the recommendations, if more detail is wanted than before
        if($this->idsOnly && !$idsOnly) $recommendationUpToDate = false;
        $this->idsOnly = $idsOnly;
        $this->getDataProvider()->setParam('idsOnly', $idsOnly ? 'true' : 'false');
    }
    
    /**
     * creates the recommendation-records
     *
     * @param string id
     * @return array of FACTFinder_Record objects
     **/
    abstract protected function createRecommendations();

    /**
     * returns recommendations for specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array
     *
     * @return FACTFinder_Result
     **/
    public function getRecommendations() {
        if (empty($this->productIds)) {
            $requestParams = $this->getParamsParser()->getRequestParams();
            if (isset($requestParams['id'])) {
                $this->productIds = array($requestParams['id']);
            }
            if (empty($this->productIds)) {
                trigger_error('recommendations can not be loaded without id. could not load id from request', E_USER_WARNING);
                return array();
            }
        }
        if (!$this->recommendationUpToDate || !isset($this->recommendation) || $this->recommendation == null) {
            $this->recommendation = $this->createRecommendations();
            $this->recommendationUpToDate = true;
        }
        return $this->recommendation;
    }
}
