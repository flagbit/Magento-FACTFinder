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

        $dom = new \DOMDocument();

        $columns[] = $this->createExportTriggerRow($dom, $store, $urlParams);

        // Download link for latest pre-generated product export
        $fileName = 'store_' . $store->getId() . '_product.csv';
        $filePath = Mage::getBaseDir('var') . DS . 'factfinder' . DS;

        if (file_exists($filePath . $fileName)) {
            $columns[] = $this->createDownloadRow($dom, $store, $urlParams);
            $columns[] = $this->createExportRow($dom, $store, $urlParams);
        }

        //Link to schedule cron export
        if (Mage::getStoreConfig('factfinder/cron/enabled')) {
            $columns[] = $this->createCronExport($dom, $store, $urlParams);
        }

        $table = $this->createTable($dom, $columns);

        $dom->appendChild($table);

        return $dom->saveHTML();
    }

    /**
     * @param DOMDocument $dom
     * @param array $links
     * @param $table
     * @return mixed
     */
    private function addRow(\DOMDocument $dom, array $links, $table)
    {
        foreach ($links as $columns) {
            $row = $dom->createElement('tr');

            foreach ($columns as $column) {
                if(is_object($column) === false) {
                    $row->appendChild($dom->createElement('td', $column));
                } else {
                    $td = $dom->createElement('td');
                    $td->appendChild($column);
                    $row->appendChild($td);
                }
            }

            $table->appendChild($row);
        }

        return $table;
    }

    /**
     * @param DOMDocument $dom
     * @param $columns
     * @return DOMElement
     */
    private function createTable(\DOMDocument $dom, array $columns)
    {
        $table = $dom->createElement('table');

        $fullTable = $this->addRow($dom, $columns, $table);

        return $fullTable;
    }

    /**
     * @param DOMDocument $dom
     * @param $store
     * @param $urlParams
     * @return array
     */
    private function createExportTriggerRow(\DOMDocument $dom, $store, $urlParams)
    {
        $exportTrigger[] = "Trigger Realtime Export";

        $exportTrigger[] = $this->createLink(
            $dom,
            $store,
            'factfinder/export/export',
            'Products',
            $urlParams
        );

        $exportTrigger[] = $this->createLink(
            $dom,
            $store,
            'factfinder/export/export',
            'CMS',
            $urlParams
        );

        return $exportTrigger;
    }

    /**
     * @param $store
     * @param $urlParams
     * @return array
     */
    private function createDownloadRow(\DOMDocument $dom, $store, $urlParams)
    {
        $downloadExport[] = "Download Last Pre-Generated Export";

        $downloadExport[] = $this->createLink(
            $dom,
            $store,
            'factfinder/export/download',
            'Products',
            $urlParams
        );

        return $downloadExport;
    }

    /**
     * @param $store
     * @param $urlParams
     * @return array
     */
    private function createExportRow(\DOMDocument $dom, $store, $urlParams)
    {

        $exportLink[] = "Export Link for FACT-Finder Wizard" ;

        // Link for FF Backend
        $exportLink[] = $this->createLink(
            $dom,
            $store,
            'factfinder/export/get',
            'Products',
            $urlParams
        );

        return $exportLink;
    }

    /**
     * @param DOMDocument $dom
     * @param $store
     * @param $urlParams
     * @return array
     */
    private function createCronExport(\DOMDocument $dom, $store, $urlParams)
    {

        $cronExport[] = "Schedule Cron Export (in 1 minute)";

        // CronExport for Products
        $cronExport[] = $this->createLink(
            $dom,
            $store,
            'factfinder/export/scheduleExport',
            'Products',
            $urlParams
        );

        return $cronExport;
    }

    /**
     * @param DOMDocument $dom
     * @param Mage_Core_Model_Store $store
     * @param $route
     * @param $text
     * @param $params
     * @return DOMElement
     */
    protected function createLink(\DOMDocument $dom, Mage_Core_Model_Store $store, $route, $text, $params)
    {

        $text = Mage::helper('factfinder')->__($text);

        $url = Mage::app()->getStore($store)->getBaseUrl() . $route . '?';

        foreach ($params as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }

        $href = rtrim($url, '&');

        $a = $dom->createElement('a', $text);
        $a->setAttribute('href', $href);

        return $a;
    }
}
