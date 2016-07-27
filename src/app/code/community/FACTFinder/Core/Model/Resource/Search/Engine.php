<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Replaces the standard search engine
 *
 * The only thing we do here is setting our own search result collection
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Resource_Search_Engine extends Mage_CatalogSearch_Model_Resource_Fulltext_Engine
{


    /**
     * Retrieve fulltext search result data collection
     *
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    public function getResultCollection()
    {
        return Mage::getResourceModel('factfinder/search_collection');
    }


}