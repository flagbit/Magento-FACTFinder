<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * product comparison adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: CompareAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_CompareAdapter extends FACTFinder_Abstract_Adapter
{protected $productIds = array();

    protected $comparableAttributes = array();
    protected $comparedRecords = array();
    protected $attributesUpToDate = false;
    protected $recordsUpToDate = false;

    protected $idsOnly = false;

    /**
     * Set ids of products to be compared
     *
     * @param array $productIds list of integers
     **/
    public function setProductIds($productIds) {
        $this->productIds = $productIds;
        $this->getDataProvider()->setParam('ids', implode(';',$this->productIds));
        $this->attributesUpToDate = false;
        $this->recordsUpToDate = false;
    }

    /**
     * Set the idsOnly request parameter
     *
     * @param bool $idsOnly
     **/
    public function setIdsOnly($idsOnly) {
        $this->idsOnly = $idsOnly;
        $this->getDataProvider()->setParam('idsOnly', $idsOnly ? 'true' : 'false');
    }

    /**
     * Adds an id to the list of products to be compared
     *
     * @param int $productId
     **/
    public function addProductId($productId) {
        $this->productIds[] = $productId;
        $this->attributesUpToDate = false;
        $this->recordsUpToDate = false;
    }

    /**
     * returns the comparable attributes for products to be compared
     *
     * @return array $comparableAttributes of strings (field names as keys, hasDifferences as values)
     **/
    public function getComparableAttributes() {
        if (!$this->attributesUpToDate || !isset($this->comparableAttributes) || $this->comparableAttributes == null) {
            $this->comparableAttributes = $this->createComparableAttributes();
            $this->attributesUpToDate == true;
        }
        return $this->comparableAttributes;
    }

    /**
     * returns the Record objects or record ids for products to be compared (depending on the value of idsOnly)
     *
     * @return array $comparedRecords list of FACTFinder_Record objects
     **/
    public function getComparedRecords() {
        if (!$this->recordsUpToDate || !isset($this->comparedRecords) || $this->comparedRecords == null) {
            $this->comparedRecords = $this->createComparedRecords();
            $this->recordsUpToDate == true;
        }
        return $this->comparedRecords;
    }

    /**
     * @return array $comparableAttributes of strings (field names as keys, hasDifferences as values)
     */
    protected function createComparableAttributes()
    {
        $this->log->debug("Product comparison not available before FF 6.6!");
        $comparableAttributes = array();
        return $comparableAttributes;
    }

    /**
     * @return array $comparedRecords list of FACTFinder_Record objects or integers (depending on the value of idsOnly)
     */
    protected function createComparedRecords()
    {
        $this->log->debug("Product comparison not available before FF 6.6!");
        $comparableAttributes = array();
        return $comparableAttributes;
    }
}
