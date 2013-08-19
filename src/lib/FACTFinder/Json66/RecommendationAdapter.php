<?php
/**
 * adapter for the factfinder recommendation engine, working with the JSON interface of FF6.6
 */
class FACTFinder_Json66_RecommendationAdapter extends FACTFinder_Default_RecommendationAdapter
{
    protected function createRecommendations() {
        $this->log->error("FF 6.6 does not provide a JSON API for its recommendation engine");
        return FF::getInstance('result', array(), 0);
	}
}
