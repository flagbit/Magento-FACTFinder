<?php

/**
 * http scic adapater
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: ScicAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_ScicAdapter extends FACTFinder_Abstract_ScicAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function init() {
        $this->log->info("Initializing new SCIC adapter.");
        $this->getDataProvider()->setType('SCIC.ff');
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean $success
     */
    protected function applyTracking() {
        $success = trim($this->getData());
        return $success == 'true';
    }
}