<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Controller class
 * 
 * This class the Proxy Controller
 * It provides a scic and a suggest Action
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_ProxyController extends Mage_Core_Controller_Front_Action {
	
    /**
     * scic Action
     */
	public function scicAction()
	{	
		$this->getResponse()->setBody(
			Mage::getModel('factfinder/processor')->handleInAppRequest($this->getFullActionName())
		);		
	}
	
    	
	
    /**
     * suggest Action
     */	
	public function suggestAction()
	{	
		$this->getResponse()->setBody(
			Mage::getModel('factfinder/processor')->handleInAppRequest($this->getFullActionName())
		);					
	}
}