<?php
class FACTFinder_Suggest_Block_TopSearch extends Mage_Core_Block_Template
{
    public function getChannelsAsJson()
    {
        $channels = explode(';', Mage::getStoreConfig('factfinder/search/secondary_channels'));
        $result = new StdClass();

        foreach($channels as $channel) {
            $result->{'Channel: ' . $channel} = $this->__('Channel: ' . $channel);
        }

        return json_encode($result);
    }
}