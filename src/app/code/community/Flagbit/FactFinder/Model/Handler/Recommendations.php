<?php
/**
 * Handles product campaign data
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: Recommendations.php 14.09.12 11:53 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_Recommendations
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_productIds = array();

    protected $_recommendations;

    public function __construct($productIds)
    {
        $this->_productIds = $productIds;
        parent::__construct();
    }

    protected function configureFacade()
    {
        $params = array();
        $params['idsOnly'] = 'true';
        $params['id'] = $this->_getIdParam();
        $this->_getFacade()->configureRecommendationAdapter($params);
    }

    public function getRecommendations()
    {
        if($this->_recommendations === null)
        {
            $this->_recommendations = $this->_getFacade()->getRecommendations();
            if ($this->_recommendations === null)
                $this->_recommendations = array();
        }
        return $this->_recommendations;
    }

    protected function _getIdParam()
    {
        if(is_array($this->_productIds))
            return $this->_productIds;
        else
            return array($this->_productIds);
    }
}
