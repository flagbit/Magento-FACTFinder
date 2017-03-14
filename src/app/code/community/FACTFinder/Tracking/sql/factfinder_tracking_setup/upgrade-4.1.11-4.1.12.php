<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */
/**
 * Install script
 *
 * Adds column for parent ID
 *
 */

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->addColumn(
        $installer->getTable('factfinder_tracking/queue'),
        'parent_product_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Parent Product ID',
        )
    );
$installer->endSetup();