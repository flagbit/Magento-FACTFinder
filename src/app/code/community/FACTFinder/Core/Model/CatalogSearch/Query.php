<?php
/**
 * Query.php
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link http://www.flagbit.de
 */

class FACTFinder_Core_Model_CatalogSearch_Query extends Mage_CatalogSearch_Model_Query
{


    /**
     * Rewrite the model to prevent savin data to the db if FF is enabled
     *
     * @return $this|\Mage_Core_Model_Abstract
     *
     * @throws \Exception
     */
    public function save()
    {
        if (Mage::helper('factfinder')->isEnabled()) {
            return $this;
        }

        return parent::save();
    }


}