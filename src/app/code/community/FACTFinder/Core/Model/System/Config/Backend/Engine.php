<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */


/**
 * Catalog search backend model
 *
 * @category    Mage
 * @package     FACTFinder_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class FACTFinder_Core_Model_System_Config_Backend_Engine extends Mage_Core_Model_Config_Data
{


    /**
     * After save call
     * Invalidate catalog search index if engine was changed
     *
     * @return FACTFinder_Core_Model_System_Config_Backend_Engine
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if (Mage::helper('core')->isModuleEnabled('Enterprise_Search')) {
            if ($this->isValueChanged()) {
                Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext')
                    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }

        return $this;
    }


}
