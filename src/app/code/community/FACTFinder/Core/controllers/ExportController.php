<?php
/**
 * Controller class
 *
 * This class the Export Controller
 * It provides a Products, Prices and Stocks Export
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
            $this->lockSemaphore();
        } catch (RuntimeException $e) {
            $this->loadLayout()
                ->renderLayout();
            return;
        }

        try {
            $exportModel = Mage::getModel('factfinder/export_product');
            $exportModel->saveExport(
                $this->_getStoreId()
            );

            $this->releaseSemaphore(); // finally-workaround
        } catch (Exception $e) {
            $this->releaseSemaphore(); // finally-workaround
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

        if (!file_exists($filePath . $fileName)) {
            $this->loadLayout()
                ->renderLayout();
            return;
        }

        echo file_get_contents($filePath . $fileName);
    }


    /**
     * Download pre-generated export files for a specific resource and store
     */
    public function downloadAction()
    {
        $resource = Mage::app()->getRequest()->getParam('resource', 'product');

        $fileName = 'store_' . $this->_getStoreId() . '_' . $resource . '.csv';
        $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'factfinder' . DS;

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
        $schedule = Mage::getModel('cron/schedule');
        $schedule->setJobCode('factfinder_generate')
            ->setCreatedAt(time())
            ->setScheduledAt(time() + 60)
            ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
            ->save();

        $this->_redirectReferer();
    }


    /**
     * Locks the semaphore
     * Throws an exception, if semaphore is already locked
     **/
    protected function lockSemaphore()
    {
        $mtime = @filemtime($this->_getLockFileName());
        $semaphoreTimeout = FACTFinderCustom_Configuration::DEFAULT_SEMAPHORE_TIMEOUT;
        if ($mtime && time() - $mtime < $semaphoreTimeout) {
            throw new RuntimeException();
        }
        @touch($this->_getLockFileName());
    }


    /**
     * Release the semaphore
     */
    protected function releaseSemaphore()
    {
        @unlink($this->_getLockFileName());
    }


    /**
     * Retrieve the name of lockfile
     *
     * @return string
     */
    protected function _getLockFileName()
    {
        return Mage::getBaseDir('var') . DS . 'locks' . DS . 'ffexport_' . $this->_getStoreId() . '.lock';
    }


}
