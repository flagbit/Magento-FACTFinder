<?php
class FACTFinder_Tracking_Model_Queue extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		$this->_init('factfinder_tracking/queue');
	}
}