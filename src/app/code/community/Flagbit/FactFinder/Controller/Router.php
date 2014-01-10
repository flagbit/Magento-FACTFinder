<?php
class Flagbit_FactFinder_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * Helper function to register the current router at the front controller. 
     * 
     * @param Varien_Event_Observer $observer The event observer for the controller_front_init_routers event
     * @event controller_front_init_routers
     */
    public function addSeoRouter($observer)
    {
        $front = $observer->getEvent()->getFront();

        $router = new Flagbit_FactFinder_Controller_Router();
        $front->addRouter('ff_seo', $router);
    }

    /**
     * Rewritten function of the standard controller. Tries to match the pathinfo on url parameters.
     * 
     * @see Mage_Core_Controller_Varien_Router_Standard::match()
     * @param Zend_Controller_Request_Http $request The http request object that needs to be mapped on Action Controllers.
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $identifier = explode('/',$request->getPathInfo());
        if (!isset($identifier[1]) || (isset($identifier[1]) && $identifier[1] != 's')) {
            return false;
        }

        if(in_array('q', $identifier))
        {
            $key = array_search('q', $identifier);
            if($key && isset($identifier[$key - 1])) {
                $query = $identifier[$key - 1];
            }
        }

        // if successfully gained url parameters, use them and dispatch ActionController action
        $request
            ->setModuleName('catalogsearch')
            ->setControllerName('result')
            ->setActionName('index')
            ->setParam('seoPath', substr($request->getPathInfo(), 2));

        if(isset($query)) {
            $request->setParam('q', $query);
        }

        $request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            trim($request->getPathInfo(), '/')
        );

        // Workaround for unsetting of parameter q in Mage_Core_Controller_Varien_Router_Standard::245
        if(isset($query) && $qkey = array_search('q', $identifier)) {
            unset($identifier[$qkey]);
            $request->setPathInfo(implode('/',$identifier));
        }

        return true;
    }
}