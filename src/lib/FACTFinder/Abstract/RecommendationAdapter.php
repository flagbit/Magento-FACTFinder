<?php

/**
 * adapter for the factfinder recommendation engine
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_RecommendationAdapter extends FACTFinder_Abstract_Adapter
{
    private $recommendations = array();
   
    /**
     * creates the recommendation-records
     *
	 * @param string id
     * @return array of FACTFinder_Record objects
    **/
    abstract protected function createRecommendations($id);

    /**
     * returns recommendations for specified id. if no id is set, try to fetch parameter 'id'.
     * if no id is available, there will be a warning raised and returning an empty array
     *
     * @param string $id (optional; if not set try to fetch from request param)
     * @return FACTFinder_Result
     *
     */
    public function getRecommendations($id = '') {
		if (empty($id)) {
			$requestParams = $this->getParamsParser()->getRequestParams();
			if (isset($requestParams['id'])) {
				$id = $requestParams['id'];
			}
			if (empty($id)) {
				trigger_error('recommendations can not be loaded without id. could not load id from request', E_USER_WARNING);
				return array();
			}
		}
        if ($this->recommendations[$id] == null) {
            $this->recommendations[$id] = $this->createRecommendations($id);
        }
        return $this->recommendations[$id];
    }
}
