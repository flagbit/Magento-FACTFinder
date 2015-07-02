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
 * Class FACTFinder_Asn_Model_Catalog_Layer
 *
 * Replaces standard layer model in order to use our own collection
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{


    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = Mage::getResourceModel('factfinder_asn/search_collection');
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }

        return $collection;
    }


}