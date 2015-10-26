<?php
namespace FACTFinder\Data;

/**
 * Represents a suggestion for single-word search.
 */
class SingleWordSearchItem extends SuggestQuery
{
    /**
     * @var Record[]
     */
    private $previewRecords = array();

    public function addPreviewRecord(
        Record $record
    ) {
        $this->previewRecords[] = $record;
    }

    /**
     * @param Record[]
     */
    public function addPreviewRecords(
        array $records
    ) {
        foreach ($records as $record)
            $this->addPreviewRecord($record);
    }

    /**
     * @return Record[]
     */
    public function getPreviewRecords()
    {
        return $this->previewRecords;
    }
}
