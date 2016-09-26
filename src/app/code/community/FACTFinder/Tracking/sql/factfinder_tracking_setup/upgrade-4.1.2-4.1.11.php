<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */
/**
 * Install script
 *
 * Change tye of the column for Product ID
 *
 */

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->modifyColumn(
        $installer->getTable('factfinder_tracking/queue'),
        'product_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'default'  => null,
            'comment'  => 'Product ID'
        )
    );
$installer->endSetup();