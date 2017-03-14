<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Adminhtml export links renderer
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Adminhtml_Exportlink extends Mage_Adminhtml_Block_System_Config_Form_Field
{


    /**
     * Get rendered link element html
     *
     * @param \Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     *
     * @throws \Exception
     * @throws \Mage_Core_Exception
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $storeId = $this->getRequest()->getParam('store');
        $websiteId = $this->getRequest()->getParam('website');

        // define which store should be used
        if ($websiteId && !$storeId) {
            $store = Mage::app()->getWebsite($websiteId)->getDefaultStore();
        } elseif (!$websiteId) {
            $store = Mage::app()->getDefaultStoreView();
        } else {
            $store = Mage::app()->getStore($storeId);
        }

        $password = $store->getConfig('factfinder/search/auth_password');
        $key = md5($password);

        $urlParams = array(
            'key' => $key,
            'store' => $store->getId()
        );

        // Realtime export link
        $html = $this->_createLink(
            $store,
            'factfinder/export/export',
            'Trigger Realtime Export',
            $urlParams
        );

        // Link to schedule cron export
        if (Mage::getStoreConfig('factfinder/cron/enabled')) {
            $html .= $this->_createLink(
                $store,
                'factfinder/export/scheduleExport',
                'Schedule Cron Export (in 1 minute)',
                $urlParams
            );
        }

        // Download link for latest pre-generated product export
        $fileName = 'store_' . $store->getId() . '_product.csv';
        $filePath = Mage::getBaseDir('var') . DS . 'factfinder' . DS;

        if (file_exists($filePath . $fileName)) {
            $html .= $this->_createLink(
                $store,
                'factfinder/export/download',
                'Download Last Pre-Generated Export',
                $urlParams
            );

            // Link for FF Backend
            $html .= $this->_createLink(
                $store,
                'factfinder/export/get',
                'Export Link for FACT-Finder Wizard',
                $urlParams
            );
        }

        return $html;
    }


    /**
     * Create html for a hyperlink
     *
     * @param Mage_Core_Model_Store $store
     * @param string                $route
     * @param string                $text
     * @param array                 $params
     *
     * @return string
     */
    protected function _createLink(Mage_Core_Model_Store $store, $route, $text, $params)
    {
        $text = Mage::helper('factfinder')->__($text);

        $url = Mage::app()->getStore($store)->getBaseUrl() . $route . '?';

        foreach ($params as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }

        $href = rtrim($url, '&');

        return "<a href=\"{$href}\" target=\"_blank\">{$text}</a><br />";
    }


}
