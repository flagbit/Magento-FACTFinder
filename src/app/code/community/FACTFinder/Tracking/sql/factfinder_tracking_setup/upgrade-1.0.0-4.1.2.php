<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */
/**
 * Install script
 *
 * Install script for Tracking queue. Orders are sent to FACT-Finder asynchronously by cronjobs.
 */

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->addColumn(
        $installer->getTable('factfinder_tracking/queue'),
        'store_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Store ID'
        )
    );
$installer->endSetup();