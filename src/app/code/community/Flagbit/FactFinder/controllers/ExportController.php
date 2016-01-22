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
 * This class the Export Controller
 * It provides a Products, Prices and Stocks Export
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_ExportController extends Mage_Core_Controller_Front_Action {
    
    const XML_AUTH_PASSWORD_PATH    = 'factfinder/search/auth_password';
    
    /**
     * handle Export Authentification
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
     * get current Store ID
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
    public function productAction()
    {
		try
		{
			$this->lockSemaphore();
		}
		catch(RuntimeException $e)
		{
			// TODO: use a proper template
			echo
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
				'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">',
				'<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>',
				'<body>',
				$this->__('Another export is already running. Please wait for it to finish before you start a new export.'), "<br>\n",
				$this->__('If you receive this message after another export has failed, please delete the file "ffexport.lock" within your Magento directory.'),
				'</body></html>';
			flush();
			return;
		}
		
		try {
			$exportModel = Mage::getModel('factfinder/export_product');
            $lines = $exportModel->doExport(
				$this->_getStoreId()
			);

            echo implode('', $lines);

			$this->releaseSemaphore(); // finally-workaround
		} catch(Exception $e) {
			$this->releaseSemaphore(); // finally-workaround
			throw $e;
		}
    }

    /**
     * Output pre-generated export files for a specific resource and store (for ff import process)
     */
    public function getAction()
    {
        $resource = Mage::app()->getRequest()->getParam('resource', 'product');

        $fileName = 'store_' . $this->_getStoreId() . '_' . $resource . '.csv';
        $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'factfinder' . DS;

        if(!file_exists($filePath . $fileName)) {
            echo
                '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
                '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">',
                '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>',
                '<body>',
                $this->__('Currently there is no pre-generated file for your resource request.'), "<br>\n",
                $this->__('Please start an export request in the backend or wait until the file is generated through the cron.'),
                '</body></html>';
                flush();
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

        if(!file_exists($filePath . $fileName)) {
            echo
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
            '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">',
            '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>',
            '<body>',
            $this->__('Currently there is no pre-generated file for your resource request.'), "<br>\n",
            $this->__('Please start an export request in the backend or wait until the file is generated through the cron.'),
            '</body></html>';
            flush();
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

    public function storesAction()
    {
        $exportModel = Mage::getModel('factfinder/export_product');
        $stores = Mage::app()->getStores();
        foreach ($stores as $id => $store ){
            $exportModel->saveExport($id);
        }
    }
    
    /**
     * Initialize Price Export 
     */    
    public function priceAction()
    {    
        $exportModel = Mage::getModel('factfinder/export_price');
        $exportModel->doExport(
            $this->_getStoreId()
        );
    }

    /**
     * Initialize Stock Export 
     */    
    public function stockAction()
    {    
        $exportModel = Mage::getModel('factfinder/export_stock');
        $exportModel->doExport(
            $this->_getStoreId()
        );
    }
	
	/**
	 * Locks the semaphore
	 * Throws an exception, if semaphore is already locked
	 **/
	protected function lockSemaphore()
	{
		$mtime = @filemtime($this->_getLockFileName());
        
        if (!class_exists('FF')) {
            require_once BP.DS.'lib'.DS.'FACTFinder'.DS.'Loader.php';
        }
        
		if($mtime && time() - $mtime < FF::getSingleton('configuration')->getSemaphoreTimeout())
		{
			throw new RuntimeException();
		}
		@touch($this->_getLockFileName());
	}
	
	/**
	 * Release the semaphore
	 **/
	protected function releaseSemaphore()
	{
		@unlink($this->_getLockFileName());
	}

    protected function _getLockFileName()
    {
        return "ffexport_".$this->_getStoreId().".lock";
    }
}