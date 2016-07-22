<?php
/**
 * FACTFinder_Recommendation
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Class FACTFinder_Recommendation_Model_Handler_Recommendations
 *
 * Handles product recommendation data
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
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
        if(Mage::getStoreConfigFlag('factfinder/config/personalization')) {
            $params['sid'] = Mage::helper('factfinder_tracking')->getSessionId();
        }
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
                Mage::helper('factfinder')->performFallbackRedirect();
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
