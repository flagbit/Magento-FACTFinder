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
 * Status Enabled Config Field Backend
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_System_Config_Backend_Enabled extends Mage_Core_Model_Config_Data {

    /**
     * Check request for errors found by Helper and Observer. It will print error messages if errors found and
     * in that case set value to 0.
     *
     * @return Flagbit_FactFinder_Model_System_Config_Backend_Enabled
     */
    protected function _beforeSave()
    {
        if (!$this->getValue()) {
            return $this;
        }

        $groups = Mage::app()->getRequest()->getPost('groups');
        if (isset($groups['search']['fields']['enabled']['errors'])) {
        	$errors = $groups['search']['fields']['enabled']['errors'];
        	if (is_array($errors)) {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('factfinder')->__('FACT-Finder cannot be activated:').' <br/>'. implode('<br/>', $errors));
        	}
        	elseif (is_string($errors)) {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('factfinder')->__('FACT-Finder cannot be activated:').' <br/>' .  $errors);
        	}
        	else {
        		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('factfinder')->__('FACT-Finder cannot be activated:'));
        	}
            $this->setValue('0');
        }
        else {
        	Mage::app()->cleanCache(array(Flagbit_FactFinder_Model_Processor::CACHE_TAG));
        }

        return $this;
    }


}
