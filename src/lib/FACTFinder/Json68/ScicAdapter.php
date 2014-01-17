<?php
/**
 * SCIC adapter using the json interface
 */
class FACTFinder_Json68_ScicAdapter extends FACTFinder_Json67_ScicAdapter
{
    protected function init()
    {
        parent::init();
        $this->getDataProvider()->setType('Tracking.ff');
    }
}
