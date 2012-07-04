<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml export link
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Flagbit_FactFinder_Block_Adminhtml_Exportlink extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
	 $shopdomain = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	 $password = Mage::getStoreConfig('factfinder/search/auth_password');
	 $store = $this->getRequest()->getParam('store');
	 if ($store) 	$storeParam = '&store='.(int)Mage::getConfig()->getNode('stores/' . $store . '/system/store/id');
	 else 		$storeParam = '';
	 $linktext = Mage::helper('factfinder')->__('Download export'); //TODO: translate
        $html = '<a href="'.$shopdomain.'factfinder/export/product?key='.md5($password).$storeParam.'" target="_blank">'.$linktext.'</a>';
        return $html;
    }
}