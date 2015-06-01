<?php
class FACTFinder_Recommendation_Model_Facade extends FACTFinder_Core_Model_Facade
{

    /**
     * Set config data to recommendations adaptor
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     */
    public function configureRecommendationAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "recommendation", $channel, $id);
    }


    /**
     * Get recommendations array
     *
     * @param string $channel
     * @param int    $id
     *
     * @return ArrayIterator
     */
    public function getRecommendations($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("recommendation", "getRecommendations", $channel, $id);
    }


}