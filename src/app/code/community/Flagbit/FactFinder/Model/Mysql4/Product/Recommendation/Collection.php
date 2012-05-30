<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Recommendation based product collection.
 *
 * Data is caught by FACT-Finder, passed to normal collection, works quite as if it was the default behavior.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Michael Türk <türk@flagbit.de>
 * @version   $Id: Search.php 678 2011-08-01 13:02:50Z rudolf_batt $
 */
class Flagbit_FactFinder_Model_Mysql4_Product_Recommendation_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    /**
     * Pass the product ids from FACT-Finder to the collection.
     *
     * @param array $recommendations Array with id information in Objects.
     */
    public function setRecommendations($recommendations)
    {
        $ids = array();
        foreach ($recommendations as $recommendationItem) {
            $ids[] = $recommendationItem->getId();
        }

        $searchHelper = Mage::helper('factfinder/search');
        $idFieldName = $searchHelper->getIdFieldName();

        $this->addAttributeToFilter($idFieldName, array('in' => $ids));

        $order = new Zend_Db_Expr($this->getConnection()->quoteInto('find_in_set(`e`.`' . $idFieldName . '`, ?)', implode(',', $ids)));
        $this->getSelect()->order($order);
        
        return $this;
    }


    /**
     * Helper function to exclude certain products that are already in the cart.
     *
     * @param array $ninIds Simple array of product ids
     */
    public function addExcludeProductFilter($ninIds)
    {
        $this->addAttributeToFilter($this->getIdFieldName(), array('nin' => $ninIds));

        return $this;
    }

}