<?php
class FACTFinder_Tracking_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct() {
        $this->_init('factfinder_tracking/queue', 'id');
    }

}