<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml66
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * search adapter using the xml interface. expects a xml formated string from the dataprovider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SearchAdapter.php 25985 2010-06-30 15:31:53Z rb $
 * @package   FACTFinder\Xml66
 */
class FACTFinder_Xml66_SearchAdapter extends FACTFinder_Xml65_SearchAdapter
{
    /**
     * create result object
     **/
    protected function createResult()
    {
        $xmlResult = $this->getData();

        return $this->getResultFromRawResult($xmlResult);
    }

    protected function getResultFromRawResult($xmlResult) {
        //init default values
        $result      = array();
        $resultCount = 0;

        //load result values from the xml element
        if (!empty($xmlResult->results)) {
            $resultCount = (int) $xmlResult->results->attributes()->count;
            $encodingHandler = $this->getEncodingHandler();

            $paging = $this->getPaging();
            $positionOffset = ($paging->getCurrentPageNumber() - 1) * $this->getProductsPerPageOptions()->getSelectedOption()->getValue();

            //load result
            $positionCounter = 1;
            foreach($xmlResult->results->record AS $rawRecord){
                // get current position
                $position = $positionOffset + $positionCounter;
                $positionCounter++;

                $result[] = $this->getRecordFromRawRecord($rawRecord, $position);
            }
        }
        return FF::getInstance('result', $result, $resultCount);
    }

    /**
     * @return array of FACTFinder_SingleWordSearchItem objects
     */
    protected function createSingleWordSearch()
	{
        $xmlResult = $this->getData();
        $singleWordSearch = array();
        if (isset($xmlResult->singleWordSearch)) {
            $encodingHandler = $this->getEncodingHandler();
            foreach ($xmlResult->singleWordSearch->item AS $item) {
                $query = $encodingHandler->encodeServerContentForPage(strval($item->attributes()->word));
                $singleWordSearchItem = FF::getInstance('singleWordSearchItem',
                    $query,
                    $this->getParamsParser()->createPageLink(array('query' => $query)),
                    intval(trim($item->attributes()->count))
                );

				//add preview records
				if (isset($item->record)) {
					$position = 1;
					foreach($item->record AS $rawRecord) {
						$record = $this->getRecordFromRawRecord($rawRecord, $position);
						$singleWordSearchItem->addPreviewRecord($record);
						$position++;
					}
				}

				$singleWordSearch[] = $singleWordSearchItem;
            }
        }
        return $singleWordSearch;
    }

	protected function getRecordFromRawRecord(SimpleXmlElement $rawRecord, $position)
	{
        $record = $this->createRecord($rawRecord, $position);

		if (isset($rawRecord->seoPath)) {
			$record->setSeoPath(strval($rawRecord->seoPath));
		}

		if (isset($rawRecord->keywords)) {
			foreach($rawRecord->keywords->keyword AS $keyword) {
				$record->addKeyword(strval($keyword));
			}
		}

		return $record;
	}
}