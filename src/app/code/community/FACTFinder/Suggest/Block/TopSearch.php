<?php
class FACTFinder_Suggest_Block_TopSearch extends Mage_Core_Block_Template
{
    public function getTranslationsAsJson()
    {
        $channels = explode(';', Mage::getStoreConfig('factfinder/search/secondary_channels'));
        $result = new StdClass();

        foreach($channels as $channel) {
            $result->{'Channel: ' . $channel} = $this->__('Channel: ' . $channel);
        }

        $result->{'searchTerm'} = $this->__('ff_searchTerm');
        $result->{'category'} = $this->__('ff_category');
        $result->{'productName'} = $this->__('ff_productName');

        return json_encode($result);
    }
}