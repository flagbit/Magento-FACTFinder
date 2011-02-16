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
 * provides Identifier Options
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_System_Config_Source_Identifier
{
	/**
	 * get Authtypes as Option Array
	 * 
	 * @return array
	 */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'entity_id',
                'label' => Mage::helper('factfinder')->__('Product ID (default)')
            ),
            array(
                'value' => 'sku',
                'label' => Mage::helper('factfinder')->__('Product SKU')
            )
        );
    }
}
