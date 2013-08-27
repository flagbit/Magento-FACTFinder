<?php
/**
 * tag cloud adapter using the json interface
 */
class FACTFinder_Json69_TagCloudAdapter extends FACTFinder_Json68_TagCloudAdapter
{
    public function init() {
        parent::init();
        $this->getDataProvider()->setType('TagCloud.ff');
    }
}
