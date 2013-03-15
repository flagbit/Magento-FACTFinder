<?php
/**
 * Checks whether the configuration is working
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: CheckStatus.php 17.09.12 15:00 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_CheckStatus
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_configArray;

    protected $_errorMessages = array();

    protected $_helper;

    protected $_secondaryChannels;

    public function __construct($configArray = null)
    {
        $this->_configArray = $configArray;
        parent::__construct();
    }

    protected function configureFacade()
    {
		// facade must be loaded prior using the included library class "FF" 
        $this->_getFacade()->configureStatusHelper();
		
        FF::getSingleton('configuration', $this->_configArray);
        $this->_secondaryChannels = FF::getSingleton('configuration')->getSecondaryChannels();

        foreach($this->_secondaryChannels AS $channel)
            $this->_getFacade()->configureStatusHelper($channel);

    }

    public function checkStatus($configuredVersion)
    {
        // uncomment to see debug output
        //ob_start();

        $statusOkay = true;
        $this->_errorMessage = array();

        $primaryStatus = $this->_getFacade()->getFactFinderStatus();
        if($primaryStatus !== FFE_OK)
        {
            $this->_errorMessages[] = $this->_retrieveErrorMessage($primaryStatus);
            $statusOkay = false;
        }
        foreach($this->_secondaryChannels AS $channel)
        {
            $secondaryStatus = $this->_getFacade()->getFactFinderStatus($channel);
            if($secondaryStatus !== FFE_OK)
            {
                $this->_errorMessages[] = $this->_retrieveErrorMessage($secondaryStatus, $channel);
                $statusOkay = false;
            }
        }

        $actualVersion = $this->_getFacade()->getActualFactFinderVersion();
        $actualVersionString = $this->_getFacade()->getActualFactFinderVersionString();

        if($statusOkay && intval($actualVersion) != 1 && $actualVersion < $configuredVersion)
        {
            $this->_errorMessages[] = $this->_getHelper()->__(
                'The configured FACT-Finder version is higher than the actual version of your FACT-Finder. '.
                'Consider upgrading your FACT-Finder, or reduce the configured version to '
            ).$actualVersionString;
            $statusOkay = false;
        }

        // uncomment to see debug output
        //$this->_errorMessages[] = ob_get_clean();

        return $statusOkay;
    }

    protected function _retrieveErrorMessage($statusCode, $channel = null)
    {
        $helper = $this->_getHelper();
        if($channel === null)
            $errorMessage = $helper->__('Error in Primary Channel') . ': ';
        else
            $errorMessage = $helper->__('Error in Channel').' "'.$channel.'": ';

        switch($statusCode)
        {
        case FFE_WRONG_CONTEXT:
            $errorMessage .= $helper->__('FACT-Finder not found on server. Please check your context setting.');
            return $errorMessage;
        case FFE_CHANNEL_DOES_NOT_EXIST:
            $errorMessage .= $helper->__('Channel does not exist or the specified user does not have sufficient rights.');
            return $errorMessage;
        case FFE_WRONG_CREDENTIALS:
            $errorMessage .= $helper->__('Could not log into FACT-Finder with the given settings. Please check username, password, prefix and postfix.');
            return $errorMessage;
        case FFE_SERVER_TIME_MISMATCH:
            $errorMessage .= $helper->__('Your server\'s clock does not agree with FACT-Finder\'s. Please make sure your clock is set correctly.');
            return $errorMessage;
        }

        $codeType = floor($statusCode / 1000) * 1000;

        switch($codeType)
        {
        case FFE_CURL_ERROR:
            $errorMessage .= $helper->__('Could not establish HTTP connection.');
            $errorMessage .= ' cURL Error Code: '.($statusCode - $codeType);
            break;
        case FFE_HTTP_ERROR:
            $errorMessage .= $helper->__('Could not contact FACT-Finder.');
            $errorMessage .= ' HTTP Status Code: '.($statusCode - $codeType);
            break;
        case FFE_FACT_FINDER_ERROR:
            $errorMessage .= $helper->__('There is a problem with FACT-Finder. Please contact FACT-Finder Support.');
            break;
        default:
            $errorMessage .= $helper->__('An unknown error has occurred. Please contact FACT-Finder Support.');
            break;
        }

        return $errorMessage;
    }

    protected function _getHelper()
    {
        if($this->_helper === null)
            $this->_helper = Mage::helper('factfinder');
        return $this->_helper;
    }

    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }
}
