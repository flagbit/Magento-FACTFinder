<?php
class FACTFinder_Json68_ProductCampaignAdapter extends FACTFinder_Json67_ProductCampaignAdapter
{
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
    
    // FF 6.8 removed the advisor from product detail and shopping cart campaigns
    protected function fillCampaignObject($campaign, $campaignData)
    {
        $this->fillCampaignWithFeedback($campaign, $campaignData);
        $this->fillCampaignWithPushedProducts($campaign, $campaignData);
    }
}
	