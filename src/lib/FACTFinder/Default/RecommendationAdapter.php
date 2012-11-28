<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * adapter for the factfinder recommendation engine, working with the XML interface of FF6.7
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_RecommendationAdapter extends FACTFinder_Abstract_Adapter
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
        if($count > 0)	$this->getDataProvider()->setParam('maxResults', $count);
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

    /**
     * Set ids of multiple products to base recommendation on
     * This feature is only available in FF 6.7 and later
     * Earlier versions will simply use the first product in the list
     *
     * @param array $productIds
     * @return void
     */
    public function setProductIds($productIds) {
        $this->productIds = array($productIds[0]);
        $this->getDataProvider()->setParam('id', $this->productIds);
        $this->recommendationUpToDate = false;
    }

    public function setIdsOnly($idsOnly) {
        // Reset the recommendations, if more detail is wanted than before
        if($this->idsOnly && !$idsOnly) $recommendationUpToDate = false;
        $this->idsOnly = $idsOnly;
        $this->getDataProvider()->setParam('idsOnly', $idsOnly ? 'true' : 'false');
    }

    /**
     * returns recommendations for specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array
     *
     * @return FACTFinder_Result
     **/
    public function getRecommendations() {
        if (empty($this->productIds)) {
            $dataProviderParams = $this->getDataProvider()->getParams();
            $requestParams = $this->getParamsParser()->getRequestParams();
            if (isset($dataProviderParams['id'])) {
                $this->productIds = $dataProviderParams['id'];
            } elseif (isset($requestParams['id'])) {
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

    /**
     * creates the recommendation-records
     *
     * @param string id
     * @return array of FACTFinder_Record objects
     **/
    protected function createRecommendations()
    {
        $this->log->debug("Recommendations not available before FF 6.0!");
        return FF::getInstance('result', array(), 0);
    }
}
