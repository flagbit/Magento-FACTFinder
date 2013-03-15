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
			$exportModel->doExport(
				$this->_getStoreId()
			);
			$this->releaseSemaphore(); // finally-workaround
		} catch(Exception $e) {
			$this->releaseSemaphore(); // finally-workaround
			throw $e;
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