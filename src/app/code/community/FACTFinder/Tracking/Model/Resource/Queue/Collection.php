<?php
class FACTFinder_Tracking_Model_Resource_Queue_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{


    /**
     * Initialization here
     */
    protected function _construct()
    {
        $this->_init('factfinder_tracking/queue')
            ->setOrder('id', 'DESC');
    }


}
