<?php

/**
 * http scic adapater
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_ScicAdapter extends FACTFinder_Abstract_ScicAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function init() {
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