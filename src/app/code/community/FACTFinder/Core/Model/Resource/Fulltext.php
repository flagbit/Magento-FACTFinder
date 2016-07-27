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
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Resource_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{


    /**
     * Override this method to prevent making unnecessary sql requests if FF is enabled
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string                            $queryText
     * @param Mage_CatalogSearch_Model_Query    $query
     *
     * @return FACTFinder_Core_Model_Resource_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        if (Mage::helper('factfinder')->isEnabled()) {
            return $this;
        }

        return parent::prepareResult($object, $queryText, $query);
    }
}