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
 * Update configuration keys
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
    UPDATE `{$installer->getTable('core/config_data')}`
        SET `path` = 'factfinder/activation/navigation' where `path` = 'factfinder/config/navigation';
    UPDATE `{$installer->getTable('core/config_data')}`
        SET `path` = 'factfinder/activation/upsell' where `path` = 'factfinder/config/upsell';
    UPDATE `{$installer->getTable('core/config_data')}`
        SET `path` = 'factfinder/activation/crosssell' where `path` = 'factfinder/config/crosssell';
");

$installer->endSetup();