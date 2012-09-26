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
 * provides FACT-Finder version options
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_System_Config_Source_Ffversion
{
	/**
	 * get FACT-Finder versions as Option Array
	 * 
	 * @return array
	 */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 68,
                'label' => '6.8'
            ),
            array(
                'value' => 67,
                'label' => '6.7'
            ),
            array(
                'value' => 66,
                'label' => '6.6'
            ),
            array(
                'value' => 65,
                'label' => '6.5'
            )
        );
    }
}
