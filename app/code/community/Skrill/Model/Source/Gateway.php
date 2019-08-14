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
 *
 * @package     Skrill
 * @copyright   Copyright (c) 2014 Skrill
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Creditcard Mode model
 * 
 */
class Skrill_Model_Source_Gateway
{
    /**
     * Define which gateway are possible
     *
     * @return array
     */
    public function toOptionArray()
    {
        $gateway = array(
            array(
                'label' => Mage::helper('skrill')->__('SKRILL_BACKEND_CH_SKRILL'),
                'value' => 'SKRILL'
            ), 
            array(
               'label' => Mage::helper('skrill')->__('SKRILL_BACKEND_CH_PAYON'),
               'value' => 'PAYON'
            )
        );
        return $gateway;
    }
}
