<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Asn_Block_Catalog_Product_List_Toolbar
 *
 * Replaces default layer toolbar on navigation
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Block_Catalog_Product_List_Toolbar extends FACTFinder_Core_Block_Catalog_Product_List_Toolbar
{


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        if (!Mage::helper('factfinder_asn')->isCatalogNavigation()) {
            $this->_useFF = false;
        }

        parent::_construct();
    }


}
