<?php
/**
 * search adapter using the json interface. expects a json formated string from the dataprovider
 */
class FACTFinder_Json68_SearchAdapter extends FACTFinder_Json67_SearchAdapter
{
    /**
     * @return array of FACTFinder_Campaign objects
     */
    protected function createCampaigns()
    {
        $campaigns = array();
        $jsonData = $this->getData();
        
        if (isset($jsonData['searchResult']['campaigns'])) {

            foreach ($jsonData['searchResult']['campaigns'] as $campaignData) {
                $campaign = $this->createEmptyCampaignObject($campaignData);
                
                $this->fillCampaignObject($campaign, $campaignData);
                
                $campaigns[] = $campaign;
            }
        }
        $campaignIterator = FF::getInstance('campaignIterator', $campaigns);
        return $campaignIterator;
    }
    
    protected function fillCampaignWithPushedProducts($campaign, $campaignData)
    {
        if (!empty($campaignData['pushedProductsRecords'])) {
            $pushedProducts = array();
            foreach ($campaignData['pushedProductsRecords'] as $recordData) {
                $record = FF::getInstance('record', $recordData['id']);
                $record->setValues($this->getEncodingHandler()->encodeServerContentForPage($recordData['record']));
                
                $pushedProducts[] = $record;
            }
            $campaign->addPushedProducts($pushedProducts);
        }
    }
}