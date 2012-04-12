<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Model class
 * 
 * FACT-Finder Search Engine Model
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_Mysql4_Search_Engine extends Mage_CatalogSearch_Model_Mysql4_Fulltext_Engine
{

    /**
     * Retrieve fulltext search result data collection
     *
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
     */
    public function getResultCollection()
    {
        return Mage::getResourceModel('factfinder/search_collection');
    }

    /**
     * Define if Layered Navigation is allowed
     *
     * @return bool
     */
    public function isLeyeredNavigationAllowed()
    {
        return true;
    }
    
    /**
     * Define if engine is avaliable
     *
     * @return bool
     */
    public function test()
    {
        return Mage::helper('factfinder/search')->getIsEnabled(false, 'asn');
    }
}
