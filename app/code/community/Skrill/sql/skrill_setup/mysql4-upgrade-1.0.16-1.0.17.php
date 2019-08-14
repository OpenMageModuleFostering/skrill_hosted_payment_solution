<?php

$installer = $this;
$installer->startSetup();
$installer->run("UPDATE {$this->getTable('sales/order_status_state')} SET is_default ='0' WHERE status = 'payment_accepted' ");
$installer->endSetup();
