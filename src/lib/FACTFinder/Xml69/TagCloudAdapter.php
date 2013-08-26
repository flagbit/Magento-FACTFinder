<?php
/**
 * tag cloud adapter using the xml interface
 */
class FACTFinder_Xml69_TagCloudAdapter extends FACTFinder_Xml68_TagCloudAdapter
{
    public function init() {
        parent::init();
        $this->getDataProvider()->setType('TagCloud.ff');
    }
}
