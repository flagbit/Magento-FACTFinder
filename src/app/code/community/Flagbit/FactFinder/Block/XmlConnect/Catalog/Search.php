<?php 
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class
 * 
 * This class is used provide FAC-Finder filters
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_XmlConnect_Catalog_Search extends Mage_XmlConnect_Block_Catalog_Search {
	
	
    /**
     * Search results xml renderer
     * XML also contains filters that can be apply (accorfingly already applyed filters and search query)
     * and sort fields
     *
     * @return string
     */
    protected function _toHtml()
    {
        if(!Mage::helper('factfinder/search')->getIsEnabled()){
    		return parent::_toHtml();
    	}     	
    	
        $searchXmlObject  = new Mage_XmlConnect_Model_Simplexml_Element('<search></search>');
        $filtersXmlObject = new Mage_XmlConnect_Model_Simplexml_Element('<filters></filters>');

        $helper = Mage::helper('catalogsearch');
        if (method_exists($helper, 'getEngine')) {
            $engine = Mage::helper('catalogsearch')->getEngine();
            $isLayeredNavigationAllowed = ($engine instanceof Varien_Object) ? $engine->isLeyeredNavigationAllowed() : true;
        }
        else {
            $isLayeredNavigationAllowed = true;
        }

        $request        = $this->getRequest();
        $requestParams  = $request->getParams();
        $hasMoreProductItems = 0;

        /**
         * Products
         */
        $productListBlock = $this->getChild('product_list');
        $productListBlock->setNeedBlockApplyingFilters(false);
        if ($productListBlock) {
            $layer = Mage::getSingleton('catalogsearch/layer');
            $productsXmlObj = $productListBlock->setLayer($layer)
                ->setNeedBlockApplyingFilters(!$isLayeredNavigationAllowed)
                ->getProductsXmlObject();
            $searchXmlObject->appendChild($productsXmlObj);
            $hasMoreProductItems = (int)$productListBlock->getHasProductItems();
        }

        $searchXmlObject->addAttribute('has_more_items', $hasMoreProductItems);

        /**
         * Filters
         */
        $showFiltersAndOrders = true;
        $reguest = $this->getRequest();
        foreach ($reguest->getParams() as $key => $value) {
            if (0 === strpos($key, parent::REQUEST_SORT_ORDER_PARAM_REFIX) ||
                0 === strpos($key, parent::REQUEST_FILTER_PARAM_REFIX)) {
                $showFiltersAndOrders = false;
                break;
            }
        }
        if ($isLayeredNavigationAllowed && $productListBlock && $showFiltersAndOrders) {
            $filters = $productListBlock->getCollectedFilters();
  
            /**
             * Render filters xml
             */
            foreach ($filters as $filter) {
                if (!count($filter->getAttributeModel()->getItems())) {
                    continue;
                }
                
                $item = $filtersXmlObject->addChild('item');
                $item->addChild('name', $searchXmlObject->xmlentities($filter->getName()));
                $item->addChild('code', $filter->getRequestVar());
                $values = $item->addChild('values');

                foreach ($filter->getAttributeModel()->getItems() as $valueArray) {
                	$valueItem = new Varien_Object($valueArray);
                    $count = (int)$valueItem->getCount();
                    if (!$count) {
                        continue;
                    }
                    $value = $values->addChild('value');
                    $value->addChild('id', base64_encode($valueItem->getValue()));
                    $value->addChild('label', $searchXmlObject->xmlentities(strip_tags($valueItem->getLabel())));
                    $value->addChild('count', $count);
                }
            }
            $searchXmlObject->appendChild($filtersXmlObject);
        }

        /**
         * Sort fields
         */
        if ($showFiltersAndOrders) {
            $searchXmlObject->appendChild($this->getProductSortFeildsXmlObject());
        }

        return $searchXmlObject->asNiceXml();
    }
	
	
}