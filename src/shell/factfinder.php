<?php

require_once 'abstract.php';

class Mage_Shell_FactFinder extends Mage_Shell_Abstract
{

    public function run()
    {
        Mage::getModel('factfinder/export_product')->saveAll();
    }

}

$shell = new Mage_Shell_FactFinder();
$shell->run();
