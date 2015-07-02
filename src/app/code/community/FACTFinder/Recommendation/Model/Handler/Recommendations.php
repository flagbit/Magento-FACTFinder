<?php

/**
 * Handles product recommendation data
 */
class FACTFinder_Recommendation_Model_Handler_Recommendations extends FACTFinder_Core_Model_Handler_Abstract
{

    /**
     * Array of product ids
     *
     * @var array
     */
    protected $_productIds = array();

    /**
     * Array of product ids
     *
     * @var array|null
     */
    protected $_recommendations;

    /**
     * Facade model to be used
     *
     * @var string
     */
    protected $_facadeModel = 'factfinder_recommendation/facade';


    /**
     * Class constructor
     *
     * @param array $productIds
     */
    public function __construct($productIds)
    {
        $this->_productIds = $productIds;
        parent::__construct();
    }


    /**
     * Set config values and configure adapter
     *
     * @return void
     */
    protected function _configureFacade()
    {
        $params = array();
        $params['id'] = $this->_getIdParam();
        $params['idsOnly'] = $this->_getFacade()->getConfiguration()->getIdsOnly() ? 'true' : 'false';
        $this->_getFacade()->configureRecommendationAdapter($params);
    }


    /**
     * Retrieve an array of recommendation result
     *
     * @return array|null
     */
    public function getRecommendations()
    {
        if ($this->_recommendations === null) {
            $this->_recommendations = $this->_getFacade()->getRecommendations();
            if ($this->_recommendations === null) {
                $this->_recommendations = array();
            }
        }

        return $this->_recommendations;
    }


    /**
     * Get array of product ids for recommendations
     *
     * @return array
     */
    protected function _getIdParam()
    {
        if (is_array($this->_productIds)) {
            return $this->_productIds;
        } else {
            return array($this->_productIds);
        }
    }


    /**
     * Get ids of recommended products
     *
     * @return array
     */
    public function getRecommendedIds()
    {
        $ids = array();
        foreach ($this->getRecommendations() as $recommendation) {
            $ids[] = $recommendation->getId();
        }

        return $ids;
    }


}
