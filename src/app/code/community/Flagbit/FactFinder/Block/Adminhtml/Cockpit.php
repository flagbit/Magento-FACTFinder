<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Black class
 * 
 * This Block class provides the FACT-Finder Business User Cockpit Authentication URL
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Adminhtml_Cockpit extends Mage_Adminhtml_Block_Template {
	
	/**
	 * get FACT-Finder Business User Cockpit Authentication URL
	 * 
	 * @return string
	 */
	public function getAuthenticationUrl()
	{
		return Mage::getSingleton('factfinder/adapter')->getAuthenticationUrl();
	}

}