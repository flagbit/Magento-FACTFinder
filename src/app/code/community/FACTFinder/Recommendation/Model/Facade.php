<?php
class FACTFinder_Recommendation_Model_Facade extends FACTFinder_Core_Model_Facade
{
    public function configureRecommendationAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "recommendation", $channel, $id);
    }

    public function getRecommendations($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("recommendation", "getRecommendations", $channel, $id);
    }
}