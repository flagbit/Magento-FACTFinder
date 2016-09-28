<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Controller class
 *
 * This class the Export Controller
 * It provides a Products, Prices and Stocks Export
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_ExportController extends Mage_Core_Controller_Front_Action
{

    const XML_AUTH_PASSWORD_PATH = 'factfinder/search/auth_password';


    /**
     * Handle Export Authentification
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function preDispatch()
    {
        $this->_getStoreId();
        $password = md5(Mage::getStoreConfig(self::XML_AUTH_PASSWORD_PATH));

        if ($password == '' || $password != $this->getRequest()->getParam('key')) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::preDispatch();
    }


    /**
     * Get instance of export semaphore
     *
     * @return FACTFinder_Core_Model_Export_Semaphore
     */
    protected function getSemaphore()
    {
        return Mage::getSingleton('factfinder/export_semaphore', array('store_id' => $this->_getStoreId()));
    }


    /**
     * Get current Store ID
     *
     * @return int
     */
    protected function _getStoreId()
    {
        if ($storeId = $this->getRequest()->getParam('store')) {
            Mage::app()->setCurrentStore($storeId);
        }

        return Mage::app()->getStore()->getId();
    }


    /**
     * Initialize Product Export
     */
    public function exportAction()
    {
        try {
            $this->getSemaphore()->lock();
        } catch (RuntimeException $e) {
            $this->loadLayout()
                ->renderLayout();
            Mage::helper('factfinder/debug')->log('Export action was locked', true);
            return;
        }

        $resource = Mage::app()->getRequest()->getParam('resource', 'product');
        Mage::helper('factfinder/debug')->log(
            'Export action called: resource=' . $resource . ', store='. $this->_getStoreId(), true);

        try {
            $exportModel = Mage::getModel('factfinder/export_type_' . $resource);
            $exportModel->saveExport(
                $this->_getStoreId()
            );

            $this->getSemaphore()->release(); // finally-workaround
        } catch (Exception $e) {
            $this->getSemaphore()->release(); // finally-workaround
            Mage::helper('factfinder/debug')->error('Export action ' . $e->__toString());
            throw $e;
        }

        $this->_forward('get');
    }


    /**
     * Output pre-generated export files for a specific resource and store (for ff import process)
     */
    public function getAction()
    {
        $resource = Mage::app()->getRequest()->getParam('resource', 'product');

        $fileName = 'store_' . $this->_getStoreId() . '_' . $resource . '.csv';
        $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'factfinder' . DS;
        Mage::helper('factfinder/debug')->log('Get action called: ' . $fileName, true);

        if (!file_exists($filePath . $fileName)) {
            $this->loadLayout()
                ->renderLayout();
            return;
        }

        $this->getResponse()->setBody(
            file_get_contents($filePath . $fileName)
        );
    }


    /**
     * Download pre-generated export files for a specific resource and store
     */
    public function downloadAction()
    {
        $resource = Mage::app()->getRequest()->getParam('resource', 'product');

        $fileName = 'store_' . $this->_getStoreId() . '_' . $resource . '.csv';
        $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'factfinder' . DS;
        Mage::helper('factfinder/debug')->log('Download action called: ' . $fileName, true);

        if (!file_exists($filePath . $fileName)) {
            $this->loadLayout()
                ->renderLayout();
            return;
        }

        $this->_prepareDownloadResponse(
            $fileName,
            array(
                'type' => 'filename',
                'value' => $filePath . $fileName
            )
        );
    }


    /**
     * Add export action to cronjob table
     */
    public function scheduleExportAction()
    {
        Mage::helper('factfinder/debug')->log('ScheduleExport action called', true);
        $schedule = Mage::getModel('cron/schedule');
        $schedule->setJobCode('factfinder_generate')
            ->setCreatedAt(time())
            ->setScheduledAt(time() + 60)
            ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
            ->save();

        $this->_redirectReferer();
    }


    /**
     * Used to forward actions from wrappers
     *
     * @param string $resource
     *
     * @return void
     */
    protected function _forwardExport($resource)
    {
        Mage::helper('factfinder/debug')->log($resource . ' export called', true);
        $params = $this->getRequest()->getParams();
        $params = array_merge($params, array(
            'resource' => $resource,
        ));

        $this->_forward('export', null, null, $params);
    }


    /**
     * Wrapper for price export
     */
    public function priceAction()
    {
        $this->_forwardExport('price');
    }


    /**
     * Wrapper ro stock export
     */
    public function stockAction()
    {
        $this->_forwardExport('stock');
    }


    /**
     * Wrapper for product export
     */
    public function productAction()
    {
        $this->_forwardExport('product');
    }


}
