<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Install script
 *
 * Install script for SCIC queue. Orders are sent to FACT-Finder asynchronously by cronjobs.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Michael Türk <tuerk@flagbit.de>
 * @version   $Id: Processor.php 647 2011-03-21 10:32:14Z rudolf_batt $
 */

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$installer->getTable('factfinder/scic_queue')}`
        ADD COLUMN `store_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
");

$installer->endSetup();