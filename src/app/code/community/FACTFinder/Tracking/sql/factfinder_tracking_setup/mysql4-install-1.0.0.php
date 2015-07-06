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
 * Install script for SCIC queue. Orders are sent to FACT-Finder asynchronously by cronjobs.
 */

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('factfinder_tracking/queue'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Product ID')
    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product Name')
    ->addColumn('sid', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(), 'Session ID')
    ->addColumn('userid', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(), 'User ID')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
        'default'  => '0.0000',
    ), 'Price')
    ->addColumn('count', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'Count')
    ->setComment('FACTFinder Tracking Queue');
$installer->getConnection()->createTable($table);

$installer->endSetup();