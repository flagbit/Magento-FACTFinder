<?php
class FACTFinder_Tagcloud_Model_Facade extends FACTFinder_Core_Model_Facade
{
    public function getTagCloudAdapter($channel = null)
    {
        return $this->_getAdapter("tagCloud", $channel);
    }

    public function configureTagCloudAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "tagCloud", $channel, $id);
    }

    public function getTagCloud($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("tagCloud", "getTagCloud", $channel, $id);
    }
}