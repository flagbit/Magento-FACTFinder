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
 * This class is used to disable Magento´s default apply Filter
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_XmlConnect_Catalog_Product_List extends Mage_XmlConnect_Block_Catalog_Product_List {

	 /**
     * Retrieve product collection with all prepared data and limitations
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if(!Mage::helper('factfinder/search')->getIsEnabled()){
    		return parent::_getProductCollection();
    	}

    	if (is_null($this->_productCollection)) {
	    	if (strpos($this->getRequest()->getActionName(), 'search') !== false) {
    			$this->__getSearchProductCollection();
            }
            else {
                parent::_getProductCollection();
	    	}
    	}
    	return $this->_productCollection;
    }


     /**
     * Retrieve product collection with all prepared data and limitations
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function __getSearchProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $filters        = array();
            $request        = $this->getRequest();
            $requestParams  = $request->getParams();
            $layer          = $this->getLayer();
            if (!$layer) {
                return null;
            }
            $category       = $this->getCategory();
            if ($category && is_object($category) && $category->getId()) {
                $layer->setCurrentCategory($category);
            }

            if (!$this->getNeedBlockApplyingFilters()) {
                $attributes     = $layer->getFilterableAttributes();
                /**
                 * Apply filters
                 */
                foreach ($attributes as $attributeItem) {
                    $attributeCode  = $attributeItem->getAttributeCode();
                    list($filterModel, $filterBlock) = $this->helper('xmlconnect')->getFilterByKey($attributeCode);

                    $filterModel->setLayer($layer)->setAttributeModel($attributeItem);

                    $filterParam = parent::REQUEST_FILTER_PARAM_PREFIX . $attributeCode;
                    /**
                     * Set new request var
                     */
                    if (isset($requestParams[$filterParam])) {
                        $filterModel->setRequestVar($filterParam);
                    }
                    $filterModel->apply($request, $filterBlock);
                    $filters[] = $filterModel;
                }

                /**
                 * Separately apply and save category filter
                 */
                list($categoryFilter, $categoryFilterBlock) = $this->helper('xmlconnect')->getFilterByKey('category');
                $filterParam = parent::REQUEST_FILTER_PARAM_PREFIX . $categoryFilter->getRequestVar();

                $categoryFilter->setLayer($layer)->setRequestVar($filterParam)
                    ->apply($this->getRequest(), $categoryFilterBlock);
                $filters[] = $categoryFilter;
                Mage::log(get_class($categoryFilter));
                $attributeModel = $categoryFilter->getData('attribute_model');
                Mage::log($attributeModel);
                Mage::log(is_null($attributeModel));
                $this->_collectedFilters = $filters;
            }

            /**
             * Products
             */
            $layer      = $this->getLayer();
            $collection = $layer->getProductCollection();

            /**
             * Add rating and review summary, image attribute, apply sort params
             */
            $this->_prepareCollection($collection);

            /**
             * Apply offset and count
             */
            $offset = (int)$request->getParam('offset', 0);
            $count  = (int)$request->getParam('count', 0);
            $count  = $count <= 0 ? 1 : $count;
            if ($offset + $count < $collection->getSize()) {
                $this->setHasProductItems(1);
            }
            $collection->getSelect()->limit($count, $offset);
            $collection->setFlag('require_stock_items', true);

            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }


}




