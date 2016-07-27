<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{

    /**
     * Handler model to process requests
     *
     * @var string
     */
    protected $_handler;


    /**
     * Initialization of the handler
     */
    protected function _construct()
    {
        $this->_handler = Mage::getSingleton('factfinder_campaigns/handler_search');

        parent::_construct();
    }


    /**
     * Set handler model
     *
     * @param $handler
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function setHandler($handler)
    {
        $this->_handler = $handler;

        return $this;
    }


    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->_getCampaigns()->getPushedProducts());
    }


    /**
     * Get campaigns
     *
     * @return FACTFinder\Data\Campaign
     */
    protected function _getCampaigns()
    {
        return $this->_handler->getCampaigns();
    }


    /**
     * Load entities records into items
     *
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @throws Exception
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {

        $productIds = array();
        $campaigns = $this->_getCampaigns();

        if (!$campaigns) {
            return $this;
        }

        foreach ($campaigns->getPushedProducts() as $record) {
            $productIds[$record->getId()] = new Varien_Object(
                array(
                    'similarity'        => $record->getSimilarity(),
                    'position'          => $record->getPosition(),
                )
            );
        }

        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();

        if (!empty($productIds)) {
            // add Filter to Query
            $this->addFieldToFilter(
                $idFieldName,
                array('in' => array_keys($productIds))
            );

            $this->_pageSize = null;

            $this->getSelect()->reset(Zend_Db_Select::LIMIT_COUNT);
            $this->getSelect()->reset(Zend_Db_Select::LIMIT_OFFSET);

            $this->printLogQuery($printQuery, $logQuery);
            Mage::helper('factfinder/debug')->log('Search SQL Query: ' . $this->getSelect()->__toString());

            try {
                $rows = $this->_fetchAll($this->getSelect());
            } catch (Exception $e) {
                Mage::printException($e, $this->getSelect());
                $this->printLogQuery(true, true, $this->getSelect());
                throw $e;
            }

            $items = array();
            foreach ($rows as $v) {
                $items[$v[$idFieldName]] = $v;
            }

            foreach ($productIds as $productId => $additionalData) {
                if (empty($items[$productId])) {
                    continue;
                }

                $v = array_merge($items[$productId], $additionalData->toArray());
                $object = $this->getNewEmptyItem()->setData($v);

                $this->addItem($object);
                if (isset($this->_itemsById[$object->getId()])) {
                    $this->_itemsById[$object->getId()][] = $object;
                } else {
                    $this->_itemsById[$object->getId()] = array($object);
                }
            }

        }

        return $this;
    }


    /**
     * Add search query filter
     *
     * @param   Mage_CatalogSearch_Model_Query $query
     *
     * @return  Mage_CatalogSearch_Model_Resource_Search_Collection
     */
    public function addSearchFilter($query)
    {
        return $this;
    }


    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        return $this;
    }


}