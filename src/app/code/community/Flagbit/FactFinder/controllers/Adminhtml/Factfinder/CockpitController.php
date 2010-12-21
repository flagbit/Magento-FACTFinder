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
class Flagbit_FactFinder_Adminhtml_Factfinder_CockpitController extends Mage_Adminhtml_Controller_Action
{
	
    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return Mage_Widget_Adminhtml_Widget_InstanceController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/factfinder_cockpit')
            ->_addBreadcrumb(Mage::helper('factfinder')->__('Catalog'),
                Mage::helper('factfinder')->__('Catalog'))
            ->_addBreadcrumb(Mage::helper('factfinder')->__('FACT-Finder Business User Cockpit'),
                Mage::helper('factfinder')->__('FACT-Finder Business User Cockpit'));
        return $this;
    }	
	
    /**
     * FACT-Finder Business User Cockpit Action
     */
    public function indexAction()
    {
        $this->_title($this->__('factfinder'))->_title($this->__('FACT-Finder Business User Cockpit'));

        $this->_initAction()
            ->renderLayout();
    }
    
    
    
}