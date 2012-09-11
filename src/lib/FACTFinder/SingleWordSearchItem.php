<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * represents a suggest single word search item with a preview of the found products for this query
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SingleWordSearchItem.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 */
class FACTFinder_SingleWordSearchItem extends FACTFinder_SuggestQuery
{
	private $previewRecords = array();

	/**
	 * @param array of FACTFinder_Record objects
	 * @return void
	 */
	public function addPreviewRecords(array $previewRecords)
	{
		$this->previewRecords += $previewRecords;
	}

	/**
	 * @param FACTFinder_Record object
	 * @return void
	 */
	public function addPreviewRecord(FACTFinder_Record $record)
	{
		$this->previewRecords[] = $record;
	}

	/**
	 * @return array of FACTFinder_Record objects or empty array if there are no preview objects
	 */
	public function getPreviewRecords()
	{
		return $this->previewRecords;
	}
}