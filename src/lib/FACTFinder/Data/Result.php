<?php
namespace FACTFinder\Data;

/**
 * This is mostly just a collection of Record objects.
 */
class Result extends \ArrayIterator
{
    /**
     * @var string
     */
    private $refKey;

    /**
     * @var int
     */
    private $foundRecordsCount;

    /**
     * @param Record[] $records The Record objects to add to the result.
     * @param string $refKey
     * @param int $foundRecordsCount Total number of records found for the
     *        search these records are from. This can be greater than
     *        count($records), because $records may just be the records from a
     *        single page, while $foundRecordsCount refers to all records found
     *        by the search.
     */
    public function __construct(
        array $records,
        $refKey = '',
        $foundRecordsCount = 0
    ) {
        parent::__construct($records);
        $this->refKey = (string)$refKey;
        $this->foundRecordsCount = (int)$foundRecordsCount;
    }

    /**
     * @return int
     */
    public function getFoundRecordsCount()
    {
        return $this->foundRecordsCount;
    }

    /**
     * @return string
     */
    public function getRefKey()
    {
        return $this->refKey;
    }

}
