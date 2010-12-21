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
 * provides Authtype Options
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_System_Config_Source_Authtype
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
                'value' => 'http',
                'label' => Mage::helper('factfinder')->__('http')
            ),
            array(
                'value' => 'simple',
                'label' => Mage::helper('factfinder')->__('simple')
            ),
            array(
                'value' => 'advanced',
                'label' => Mage::helper('factfinder')->__('advanced')
            )
        );
    }
}
