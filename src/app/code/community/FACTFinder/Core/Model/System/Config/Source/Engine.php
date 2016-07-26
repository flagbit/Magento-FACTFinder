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
 * Catalog search types
 *
 * @category    Mage
 * @package     FACTFinder_Core
 * @author      Flagbit Magento Team <magento@flagbit.de>
 */
class FACTFinder_Core_Model_System_Config_Source_Engine
{


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $engines = array(
            'factfinder/search_engine'      => Mage::helper('factfinder')->__('FACT-Finder'),
            'catalogsearch/fulltext_engine' => Mage::helper('factfinder')->__('MySql Fulltext'),
        );

        if (Mage::helper('core')->isModuleEnabled('Enterprise_Search')) {
            $engines = array_merge($engines,
                array(
                    'enterprise_search/engine' => Mage::helper('enterprise_search')->__('Solr'),
                ));
        }

        $options = array();
        foreach ($engines as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }

        return $options;
    }


}

