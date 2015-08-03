<?php
/**
 * FACTFinder_TagCloud
 *
 * @category Mage
 * @package FACTFinder_TagCloud
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * @category Mage
 * @package FACTFinder_TagCloud
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tagcloud_Model_Handler_TagCloud extends FACTFinder_Core_Model_Handler_Abstract
{

    /**
     * @var array
     */
    protected $_tagCloud;


    /**
     * Get an instance of FACT-Finder facade
     *
     * @return FACTFinder_Tagcloud_Model_Facade
     */
    protected function _getFacade()
    {
        if ($this->_facade === null) {
            $this->_facade = Mage::getSingleton('factfinder_tagcloud/facade');
        }

        return $this->_facade;
    }


    /**
     * Configure adapter
     *
     * @return void
     */
    protected function _configureFacade()
    {
        // Registering the needed tag cloud adapter
        $this->_getFacade()->configureTagCloudAdapter(array());
    }


    /**
     * Get terms array
     *
     * @return array
     */
    public function getTerms()
    {
        $tagCloud = $this->_getTagCloud();

        $terms = array();
        foreach ($tagCloud as $item) {
            $terms[] = new Varien_Object($this->assembleTermArray($item));
        }

        return $terms;
    }


    /**
     * Convert ff item to array
     *
     * @param \FACTFinder\Data\TagQuery $item
     *
     * @return array
     */
    protected function assembleTermArray($item)
    {
        $termArray = array(
            'name'       => $item->getLabel(),
            'url'        => $item->getUrl(),
            'popularity' => $item->getWeight(),
            'ratio'      => $item->getWeight(),
        );

        return $termArray;
    }


    /**
     * Retrieve tag cloud array
     *
     * @return array
     */
    protected function _getTagCloud()
    {
        if ($this->_tagCloud === null) {
            $this->_tagCloud = $this->_getFacade()->getTagCloud();
            if ($this->_tagCloud === null) {
                Mage::helper('factfinder')->performFallbackRedirect();
            }
        }

        return $this->_tagCloud;
    }


}

