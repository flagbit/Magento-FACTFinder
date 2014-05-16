<?php
/**
 * SCIC adapter using the xml interface
 */
class FACTFinder_Xml68_ScicAdapter extends FACTFinder_Xml67_ScicAdapter
{
    protected function init()
    {
        parent::init();
        $this->getDataProvider()->setType('Tracking.ff');
    }
}
