<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * similar records adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SimilarRecordsAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_SimilarRecordsAdapter extends FACTFinder_Abstract_Adapter
{
    protected $productId;
    private $similarAttributes;
    private $similarRecords;
    protected $attributesUpToDate = false;
    protected $recordsUpToDate = false;

    protected $idsOnly = false;

    /*
     * Option for XML query. 0 means "no maximum".
     */
    private $maxRecordCount = 0;

    /**
     * Set id of product to get similar records for
     *
     * @param int $productId
     **/
    public function setProductId($productId) {
        $this->productId = $productId;
        $this->getDataProvider()->setParam('id', $productId);
        $this->attributesUpToDate = false;
        $this->recordsUpToDate = false;
    }

    /*
     * @return int $maxRecordCount
     */
    public function getMaxRecordCount() {
        return $this->maxRecordCount;
    }

    /*
     * @param int $count positive integer (negative will be treated as 0)
     */
    public function setMaxRecordCount($count) {
        $this->maxRecordCount = $count < 1 ? 0 : $count;
        if($count > 0)	$this->getDataProvider()->setParam('maxRecordCount', $count);
        else $this->getDataProvder()->unsetParam('maxRecordCount');
    }

    public function setIdsOnly($idsOnly) {
        // Reset the similar records, if more detail is wanted than before
        if($this->idsOnly && !$idsOnly) $this->recordsUpToDate = false;
        $this->idsOnly = $idsOnly;
        if($idsOnly)
            $this->getDataProvider()->setParam('idsOnly','true');
        else
            $this->getDataProvider()->unsetParam('idsOnly');
    }

    /**
     * returns similar attributes for previously specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array

     * @return array $similarAttributes of strings (field names as keys)
     */
    public function getSimilarAttributes() {
        if (strlen($this->productId) == 0) {
            $requestParams = $this->getParamsParser()->getRequestParams();
            if (isset($requestParams['id'])) {
                $this->productId = $requestParams['id'];
            }
            if (strlen($this->productId) == 0) {
                trigger_error('recommendations can not be loaded without id. could not load id from request', E_USER_WARNING);
                return array();
            }
        }
        if (!$this->attributesUpToDate || !isset($this->similarAttributes) || $this->similarAttributes == null) {
            $this->similarAttributes = $this->createSimilarAttributes();
            $this->attributesUpToDate = true;
        }
        return $this->similarAttributes;
    }

    /**
     * returns similar records for specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array.
     *
     * @param string $id (optional; if not set try to fetch from request param)
     * @return array $similarRecords list of FACTFinder_Record objects
     */
    public function getSimilarRecords() {
        if (strlen($this->productId) == 0) {
            $requestParams = $this->getParamsParser()->getRequestParams();
            if (isset($requestParams['id'])) {
                $this->productId = $requestParams['id'];
            }
            if (strlen($this->productId) == 0) {
                trigger_error('similar records can not be loaded without id. could not load id from request', E_USER_WARNING);
                return array();
            }
        }
        if (!$this->recordsUpToDate || !isset($this->similarRecords) || $this->similarRecords == null) {
            $this->similarRecords = $this->createSimilarRecords();
            $this->recordsUpToDate = true;
        }
        return $this->similarRecords;
    }
    /**
     * @param string id of the product which should be used to get similar attributes
     * @return array $similarAttributes of strings (field names as keys)
     **/
    protected function createSimilarAttributes() {
        $this->log->debug("Similar records not supported before FACT-Finder 6.6!");
        $similarAttributes = array();
        return $similarAttributes;
    }

    /**
     * @param string id of the product which should be used to get similar records
     * @return array $similarRecords list of FACTFinder_Record items
     **/
    protected function createSimilarRecords() {
        $this->log->debug("Similar records not supported before FACT-Finder 6.6!");
        $similarRecords = array();
        return $similarRecords;
    }
}
