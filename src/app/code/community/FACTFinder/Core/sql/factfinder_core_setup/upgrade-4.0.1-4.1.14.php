<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->addColumn(
        $installer->getTable('cms/page'),
        'skip_ff_export',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 1,
            'default'  => 0,
            'comment'  => 'If page should be skipped in export'
        )
    );
$installer->endSetup();