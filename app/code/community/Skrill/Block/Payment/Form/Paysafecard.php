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

class Skrill_Block_Payment_Form_Paysafecard extends Skrill_Block_Payment_Form_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_logoAlt = Mage::helper('skrill')->__('FRONTEND_PM_PAYSAFECARD');
        $this->_logoUrl = "https://www.skrill.com/fileadmin/content/images/brand_centre/paysafecard_Logos/logo_paysafecard_blue_claim_RGB.gif";
        parent::_construct();
    }

}