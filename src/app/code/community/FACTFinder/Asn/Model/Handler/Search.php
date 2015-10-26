<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Asn_Model_Handler_Search
 *
 * Handle navigation data and data communications
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Model_Handler_Search extends FACTFinder_Core_Model_Handler_Search
{

    /**
     * Navigation array from fact finder
     *
     * @var array
     */
    protected $_afterSearchNavigation = array();

    /**
     * @var array
     */
    protected $_currentFactFinderCategoryPath = array();


    /**
     * Get and format navigation array from fact finder
     *
     * @return array
     */
    public function getAfterSearchNavigation()
    {
        if (empty($this->_afterSearchNavigation)) {
            $result = $this->_getFacade()->getAfterSearchNavigation();

            if ($result instanceof FACTFinder\Data\AfterSearchNavigation && count($result)) {
                foreach ($result as $row) {
                    $this->_afterSearchNavigation[] = array(
                        'attribute_code' => $row->getName(),
                        'name'           => $row->getName(),
                        'unit'           => $row->getUnit(),
                        'items'          => $this->_getAttributeOptions($row, $row->getUnit()),
                        'count'          => $row->count(),
                        'type'           => $this->_getFilterType($row),
                        'store_label'    => $row->getName(),
                        'link_count'     => $row->getDetailedLinkCount(),
                        'is_multiselect' => $row->isMultiSelectStyle()
                    );
                }
            }
        }

        return $this->_afterSearchNavigation;
    }


    /**
     * Get Attribute Options Array from FactFinder FilterGroupItems
     *
     * @param FACTFinder\Data\FilterGroup $filterGroup
     *
     * @return array
     */
    protected function _getAttributeOptions(FACTFinder\Data\FilterGroup $filterGroup)
    {
        $attributeOptions = array();

        $currentCategoryPath = $this->getCurrentFactFinderCategoryPath();

        foreach ($filterGroup as $option) {
            $queryParams = Mage::helper('factfinder_asn')->getQueryParams($option->getUrl());
            $queryParams = $this->_removeCategoriesFromParams($currentCategoryPath, $queryParams);

            $filterValue = $this->_getAttributeOptionValue($option, $filterGroup);

            $seoPath = '';
            if (isset($queryParams['seoPath'])) {
                $seoPath = $queryParams['seoPath'];
            }

            if ($this->_isOnSearchPage() || !empty($_filterValue)) {
                unset($queryParams['seoPath']);
            }

            if ($filterGroup->isSliderStyle()) {
                $queryParams['filter' . $option->getFieldName()] = $filterValue;
                $attributeOptions[] = $this->_prepareSliderOption($option, $filterGroup, $queryParams);
            } else {
                if (!$option->getLabel() || $this->_isTopLevelNavigation($option, $currentCategoryPath)) {
                    continue;
                }

                $attributeOptionData = $this->_prepareOption($option, $filterGroup, $filterValue);
                $attributeOptionData['seoPath'] = $seoPath;
                $attributeOptionData['queryParams'] = $queryParams;

                $attributeOptions[] = $attributeOptionData;
            }
        }

        return $attributeOptions;
    }


    /**
     * Check if this is a top level navigation option
     *
     * @param \FACTFinder\Data\Filter $option
     * @param array                   $categoryPath
     *
     * @return bool
     */
    protected function _isTopLevelNavigation(FACTFinder\Data\Filter $option, $categoryPath)
    {
        if (!$this->_isOnSearchPage()
            && strpos($option->getFieldName(), 'categoryROOT') !== false
            && in_array($option->getLabel(), $categoryPath)
        ) {
            return true;
        }

        return false;
    }


    /**
     * Remove current categories from query params
     *
     * @param array $categoryPath
     * @param array $params
     *
     * @return array
     */
    protected function _removeCategoriesFromParams($categoryPath, $params)
    {
        if (!$this->_isOnSearchPage()) {
            foreach ($categoryPath as $filterParam => $filterValue) {
                if (isset($params[$filterParam])) {
                    unset($params[$filterParam]);
                }
            }

            if (isset($params['q']) && Mage::app()->getRequest()->getModuleName() == 'catalog') {
                unset($params['q']);
            }
        }

        return $params;
    }


    /**
     * Prepare option array for slider
     *
     * @param \FACTFinder\Data\SliderFilter $option
     * @param \FACTFinder\Data\FilterGroup  $filterGroup
     * @param array                         $params
     *
     * @return array
     */
    protected function _prepareSliderOption(FACTFinder\Data\SliderFilter $option, $filterGroup, $params)
    {
        $option = array(
            'type'         => 'number',
            'label'        => 'slider',
            'value'        => $this->_getAttributeOptionValue($option, $filterGroup),
            'absolute_min' => $option->getAbsoluteMinimum(),
            'absolute_max' => $option->getAbsoluteMaximum(),
            'selected_min' => $option->getSelectedMinimum(),
            'selected_max' => $option->getSelectedMaximum(),
            'count'        => true,
            'selected'     => false,
            'requestVar'   => 'filter' . $option->getFieldName(),
            'queryParams'  => $params
        );

        return $option;
    }


    /**
     * Prepare normal option array
     *
     * @param \FACTFinder\Data\Filter      $option
     * @param \FACTFinder\Data\FilterGroup $filterGroup
     * @param string                       $filterValue
     *
     * @return array
     */
    protected function _prepareOption(FACTFinder\Data\Filter $option, $filterGroup, $filterValue)
    {
        $label = $option->getLabel();
        if ($filterGroup->getUnit()) {
            $label .= ' ' . $filterGroup->getUnit();
        }

        $option = array(
            'type'         => 'attribute',
            'label'        => $label,
            'value'        => $filterValue,
            'count'        => $option->getMatchCount(),
            'selected'     => $option->isSelected(),
            'clusterLevel' => $option->getClusterLevel(),
            'requestVar'   => 'filter' . $option->getFieldName(),
            'previewImage' => $option->getPreviewImage()
        );

        return $option;
    }


    /**
     * Check if we are on search page
     *
     * @return bool
     */
    protected function _isOnSearchPage()
    {
        return Mage::helper('factfinder/search')->getIsOnSearchPage();
    }


    /**
     * Prepare current category path array
     *
     * @return array
     */
    public function getCurrentFactFinderCategoryPath()
    {
        if (empty($this->_currentFactFinderCategoryPath)) {

            if (!Mage::registry('current_category')) {
                return array();
            }

            /** @var $category Mage_Catalog_Model_Category */
            $category = Mage::registry('current_category');

            $pathInStore = $category->getPathInStore();
            $pathIds = array_reverse(explode(',', $pathInStore));

            $categories = $category->getParentCategories();
            $mainCategoriesString = '';
            foreach ($pathIds as $categoryId) {
                if (!isset($categories[$categoryId]) || !$categories[$categoryId]->getName()) {
                    continue;
                }

                $categoryName = html_entity_decode($categories[$categoryId]->getName());
                if (empty($mainCategoriesString)) {
                    $this->_currentFactFinderCategoryPath['filtercategoryROOT'] = $categoryName;
                } else {
                    $this->_currentFactFinderCategoryPath['filtercategoryROOT' . $mainCategoriesString] = $categoryName;
                }

                $mainCategoriesString .= '/' . $this->_encodeSpecialCharacters($categoryName);
            }
        }

        return $this->_currentFactFinderCategoryPath;
    }


    /**
     * Get Attribute option Value
     *
     * @param FACTFinder\Data\Filter      $option
     * @param FACTFinder\Data\FilterGroup $filterGroup
     *
     * @return string
     */
    protected function _getAttributeOptionValue(FACTFinder\Data\Filter $option, $filterGroup)
    {
        $value = null;

        if ($filterGroup->isSliderStyle()) {
            $value = '[VALUE]';
        } else {
            $queryParams = Mage::helper('factfinder_asn')->getQueryParams($option->getUrl());

            if (isset($queryParams['filter' . $option->getFieldName()])) {
                $value = $queryParams['filter' . $option->getFieldName()];
            } else {
                $value = '';
            }
        }

        return $value;
    }


    /**
     * Get Filter Type by FACT-Finder FilterItem
     *
     * @param FACTFinder\Data\FilterGroup $options
     *
     * @return string
     */
    protected function _getFilterType(FACTFinder\Data\FilterGroup $options)
    {
        $type = 'text';
        if ($options->isSliderStyle()) {
            $type = 'slider';
        }

        return $type;
    }


    /**
     * Prepare all request parameters for the search adapter
     *
     * @return array
     */
    protected function _collectParams()
    {
        $params = parent::_collectParams();

        if (Mage::app()->getRequest()->getModuleName() == 'catalog') {
            if (!Mage::app()->getRequest()->getParam('advisorStatus')) {
                $params = array_merge($params, $this->getCurrentFactFinderCategoryPath());
            }

            $params['navigation'] = 'true';
        }

        return $params;
    }


    /**
     * Encode special characters according to ff list
     *
     * @param string $categoryName
     *
     * @return string
     */
    protected function _encodeSpecialCharacters($categoryName)
    {
        $categoryName = str_replace(
            array('%', '#', '|', '/', '=', '+'),
            array('%25', '%23', '%7C', '%2F', '%3D', '%2B'),
            $categoryName
        );

        return $categoryName;
    }


}