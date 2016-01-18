<?php

require_once 'abstract.php';

class Mage_Shell_FactFinder extends Mage_Shell_Abstract
{

    public function run()
    {
        if ($this->getArg('exportAll') || $this->getArg('exportall')) {
            $files = Mage::getModel('factfinder/export_product')->saveAll();
            echo "Successfully generated the following files:\n";
            foreach($files as $file) {
                echo $file . "\n";
            }
        } elseif ($this->getArg('exportStore')) {
            if(!is_numeric($this->getArg('exportStore'))) {
                echo $this->usageHelp();
                return;
            }
            $file = Mage::getModel('factfinder/export_product')->saveExport($this->getArg('exportStore'));
            echo 'Successfully generated export to: ' . $file . "\n";
        } elseif ($this->getArg('exportStorePrice')) {
            if(!is_numeric($this->getArg('exportStorePrice'))) {
                echo $this->usageHelp();
                return;
            }
            $file = Mage::getModel('factfinder/export_price')->saveExport($this->getArg('exportStorePrice'));
            echo 'Successfully generated price export to: ' . $file . "\n";
        } elseif ($this->getArg('exportStoreStock')) {
            if(!is_numeric($this->getArg('exportStoreStock'))) {
                echo $this->usageHelp();
                return;
            }
            $file = Mage::getModel('factfinder/export_stock')->saveExport($this->getArg('exportStoreStock'));
            echo 'Successfully generated stock export to: ' . $file . "\n";
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php factfinder.php -- [options]

  --exportAll                   Export products for every store
  --exportStore <storeId>       Show Indexer(s) Index Mode
  --exportStorePrice <storeId>  Export Price CSV For Store
  --exportStoreStock <storeId>  Export Stock CSV For Store
  exportall                     Export products for every store
  help                          This help

  <storeId>                     Id of the store you want to export

USAGE;
    }
}

$shell = new Mage_Shell_FactFinder();
$shell->run();
