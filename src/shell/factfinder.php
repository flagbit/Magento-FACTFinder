<?php

require_once 'abstract.php';

class Mage_Shell_FactFinder extends Mage_Shell_Abstract
{
    const EXPORT_ALL_TYPES_FOR_STORE      = 'exportAllTypesForStore';
    const EXPORT_ALL_TYPES_FOR_ALL_STORES = 'exportAllTypesForAllStores';
    const EXPORT_STORE_PRICE              = 'exportStorePrice';
    const EXPORT_STORE_STOCK              = 'exportStoreStock';
    const EXPORT_STORE                    = 'exportStore';


    public function run()
    {
        if ($this->getArg('exportAll') || $this->getArg('exportall')) {
            $this->exportAll();
        } elseif ($this->getArg(self::EXPORT_STORE)) {
            $this->exportStore();
        } elseif ($this->getArg(self::EXPORT_STORE_PRICE)) {
            $this->exportStorePrice();
        } elseif ($this->getArg(self::EXPORT_STORE_STOCK)) {
            $this->exportStoreStock();
        } elseif ($this->getArg(self::EXPORT_ALL_TYPES_FOR_STORE)) {
            $this->expotAllTypesForStore();
        } elseif ($this->getArg(self::EXPORT_ALL_TYPES_FOR_ALL_STORES)) {
            $this->expotAllTypesForAllStores();
        } else {
            echo $this->usageHelp();
        }
    }


    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php factfinder.php -- [options]

  --exportAll                         Export products for every store
  --exportStore <storeId>             Show Indexer(s) Index Mode
  --exportStorePrice <storeId>        Export Price CSV For Store
  --exportStoreStock <storeId>        Export Stock CSV For Store
  --exportAllTypesForStore <storeId>  Export Stock, Price and Products for store
  --exportAllTypesForAllStores        Export Stock, Price and Products for all stores
  exportall                           Export products for every store
  help                                Show this help message

  <storeId>     Id of the store you want to export

USAGE;
    }


    /**
     * Export all types for specified store
     *
     * @return void
     */
    private function expotAllTypesForStore()
    {
        if (!is_numeric($this->getArg(self::EXPORT_ALL_TYPES_FOR_STORE))) {
            echo $this->usageHelp();
            return;
        }

        foreach (array('stock', 'price', 'product') as $type) {
            $file = Mage::getModel('factfinder/export_' . $type)
                ->saveExport($this->getArg(self::EXPORT_ALL_TYPES_FOR_STORE));
            printf("Successfully generated %s export to: %s\n", $type, $file);
        }
    }


    /**
     * Export all types for all stores
     *
     * @return void
     */
    private function expotAllTypesForAllStores()
    {
        foreach (array('stock', 'price', 'product') as $type) {
            $files = Mage::getModel('factfinder/export_' . $type)
                ->saveAll();
            foreach ($files as $file) {
                printf("Successfully generated %s export to: %s\n", $type, $file);
            }
        }
    }


    /**
     * Export stock for store
     *
     * @return void
     */
    private function exportStoreStock()
    {
        if (!is_numeric($this->getArg(self::EXPORT_STORE_STOCK))) {
            echo $this->usageHelp();
            return;
        }

        $file = Mage::getModel('factfinder/export_stock')->saveExport($this->getArg(self::EXPORT_STORE_STOCK));
        printf("Successfully generated stock export to: %s\n", $file);
    }


    /**
     * Export price for store
     *
     * @return void
     */
    private function exportStorePrice()
    {
        if (!is_numeric($this->getArg(self::EXPORT_STORE_PRICE))) {
            echo $this->usageHelp();
            return;
        }

        $file = Mage::getModel('factfinder/export_price')->saveExport($this->getArg(self::EXPORT_STORE_PRICE));
        printf("Successfully generated price export to: %s\n", $file);
    }


    /**
     * Export products for store
     *
     * @return void
     */
    private function exportStore()
    {
        if (!is_numeric($this->getArg(self::EXPORT_STORE))) {
            echo $this->usageHelp();
            return;
        }

        $file = Mage::getModel('factfinder/export_product')->saveExport($this->getArg(self::EXPORT_STORE));
        printf("Successfully generated export to: %s\n", $file);
    }


    /**
     * Export products for all stores
     *
     * @return void
     */
    private function exportAll()
    {
        $files = Mage::getModel('factfinder/export_product')->saveAll();
        echo "Successfully generated the following files:\n";
        foreach ($files as $file) {
            echo $file . "\n";
        }
    }


}

$shell = new Mage_Shell_FactFinder();
$shell->run();
