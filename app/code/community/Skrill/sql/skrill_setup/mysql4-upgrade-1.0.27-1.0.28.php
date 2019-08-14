<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @package     Skrill
 * @copyright   Copyright (c) 2013 Skrill
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();
$installer->run("
   INSERT INTO  `{$this->getTable('sales/order_status')}` (
        `status` ,
        `label`
    ) SELECT 'invalid_credential','Invalid Credential' FROM dual WHERE NOT EXISTS (SELECT * FROM `{$this->getTable('sales/order_status')}` WHERE `status` = 'invalid_credential' AND `label` = 'Invalid Credential');
   INSERT INTO  `{$this->getTable('sales/order_status_state')}` (
        `status` ,
        `state` ,
        `is_default`
    ) SELECT 'invalid_credential','new','0' FROM dual WHERE NOT EXISTS (SELECT * FROM `{$this->getTable('sales/order_status_state')}` WHERE `status` = 'invalid_credential' AND `state` = 'new');
");
$installer->endSetup();
