<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Asn_Model_Resource_Search_Collection
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Model_Resource_Search_Collection extends FACTFinder_Core_Model_Resource_Search_Collection
{


    /**
     * Get FACT-Finder Facade
     *
     * @return FACTFinder_Core_Model_Handler_Search
     */
    protected function _getSearchHandler()
    {
        return Mage::getSingleton('factfinder_asn/handler_search');
    }


}