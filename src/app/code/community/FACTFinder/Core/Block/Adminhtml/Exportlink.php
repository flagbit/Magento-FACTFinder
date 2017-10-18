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
     * @var Mage_Core_Model_Store
     */
    private $store = null;

    /**
     * @var Mage_Core_Model_Store|Mage_Core_Model_Website
     */
    private $scope = null;

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

        $password = $this->getConfig('factfinder/search/auth_password');
        $key = md5($password);

        $urlParams = array(
            'key' => $key,
            'store' => $this->getStore()->getId(),
        );

        $dom = new \DOMDocument();

        $columns[] = $this->createExportTriggerRow($dom, $urlParams);

        // Download link for latest pre-generated product export
        $fileName = 'store_' . $this->getStore()->getId() . '_product.csv';
        $filePath = Mage::getBaseDir('var') . DS . 'factfinder' . DS;

        if (file_exists($filePath . $fileName)) {
            $columns[] = $this->createDownloadRow($dom, $urlParams);
            $columns[] = $this->createExportRow($dom, $urlParams);
        }

        //Link to schedule cron export
        if ($this->getConfig('factfinder/cron/enabled')) {
            $columns[] = $this->createCronExport($dom, $urlParams);
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
     * @param $urlParams
     * @return array
     */
    private function createExportTriggerRow(\DOMDocument $dom, $urlParams)
    {
        $exportTrigger[] = "Trigger Realtime Export";

        $urlParams["resource"] = "product";
        $exportTrigger[] = $this->createLink(
            $dom,
            'factfinder/export/export',
            'Products',
            $urlParams
        );

        if($this->getConfig('factfinder/export/export_cms_pages') === '1') {
            $urlParams["resource"] = "cms";
            $exportTrigger[] = $this->createLink(
                $dom,
                'factfinder/export/export',
                'CMS',
                $urlParams
            );
        }


        return $exportTrigger;
    }

    /**
     * @param $urlParams
     * @return array
     */
    private function createDownloadRow(\DOMDocument $dom, $urlParams)
    {
        $downloadExport[] = "Download Last Pre-Generated Export";

        $urlParams["resource"] = "product";
        $downloadExport[] = $this->createLink(
            $dom,
            'factfinder/export/download',
            'Products',
            $urlParams
        );

        return $downloadExport;
    }

    /**
     * @param $urlParams
     * @return array
     */
    private function createExportRow(\DOMDocument $dom, $urlParams)
    {
        $exportLink[] = "Export Link for FACT-Finder Wizard" ;
        $urlParams["resource"] = "product";

        // Link for FF Backend
        $exportLink[] = $this->createLink(
            $dom,
            'factfinder/export/get',
            'Products',
            $urlParams
        );

        return $exportLink;
    }

    /**
     * @param DOMDocument $dom
     * @param $urlParams
     * @return array
     */
    private function createCronExport(\DOMDocument $dom, $urlParams)
    {

        $cronExport[] = "Schedule Cron Export (in 1 minute)";
        $urlParams["resource"] = "product";

        // CronExport for Products
        $cronExport[] = $this->createLink(
            $dom,
            'factfinder/export/scheduleExport',
            'Products',
            $urlParams
        );

        return $cronExport;
    }

    /**
     * @param DOMDocument $dom
     * @param $route
     * @param $text
     * @param $params
     * @return DOMElement
     */
    protected function createLink(\DOMDocument $dom, $route, $text, $params)
    {
        $text = Mage::helper('factfinder')->__($text);

        $url = $this->getStore()->getBaseUrl() . $route . '?';

        foreach ($params as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }

        $href = rtrim($url, '&');

        $a = $dom->createElement('a', $text);
        $a->setAttribute('href', $href);

        return $a;
    }


    /**
     * @return Mage_Core_Model_Store
     */
    private function getStore()
    {

        if($this->store === null) {

            $storeId = $this->getRequest()->getParam('store');
            $websiteId = $this->getRequest()->getParam('website');

            if($storeId) {

                $this->store = Mage::app()->getStore($storeId);
            }
            else if ($websiteId) {

                $this->store = Mage::app()->getWebsite($websiteId)->getDefaultStore();
            }
            else {

                $this->store = Mage::app()->getStore(0);
            }
        }

        return $this->store;
    }


    /**
     * @param string $path
     * @return mixed|null|string
     */
    private function getConfig($path)
    {

        if($this->scope === null) {

            $storeId = $this->getRequest()->getParam('store');
            $websiteId = $this->getRequest()->getParam('website');

            if($storeId) {

                $this->scope = Mage::app()->getStore($storeId);
            }
            else if ($websiteId) {

                $this->scope = Mage::app()->getWebsite($websiteId);
            }
            else {

                $this->scope = Mage::app()->getStore(0);
            }
        }

        return $this->scope->getConfig($path);
    }
}
