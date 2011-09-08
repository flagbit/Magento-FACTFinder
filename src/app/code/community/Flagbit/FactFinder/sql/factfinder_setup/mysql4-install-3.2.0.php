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
    CREATE TABLE `{$installer->getTable('factfinder/scic_queue')}` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `product_id` varchar(64) NOT NULL DEFAULT '',
        `sid` varchar(32) NOT NULL DEFAULT '',
        `userid` varchar(32) NOT NULL DEFAULT '',
        `price` decimal(12,4) NOT NULL default '0.0000',
        `count` smallint(5) unsigned NOT NULL default '1',

        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB ;
");

$installer->endSetup();