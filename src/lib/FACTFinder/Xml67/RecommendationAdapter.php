<?php

/**
 * adapter for the factfinder recommendation engine, working with the XML interface of FF6.7
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Xml67
 */
class FACTFinder_Xml67_RecommendationAdapter extends FACTFinder_Xml66_RecommendationAdapter
{
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
