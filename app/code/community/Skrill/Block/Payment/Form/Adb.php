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


class Skrill_Block_Payment_Form_Adb extends Skrill_Block_Payment_Form_Abstract
{
    /**
     * Method logo height
     *
     * @var string
     */
    protected $_logoHeight = '25';

    /**
     * Constructor. Set title, logo and template.
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('skrill/form.phtml');
    }

    /**
     * Retrieves the logo html
     *
     * @return string
     */
    protected function _getLogoHtml()
    {
        return $this->_getLogoTitle();
    }

    /**
     * Retrieves the logo title
     *
     * @return string
     */
    protected function _getLogoTitle()
    {
        return '<span class="adb_title">'.
           Mage::helper('skrill')->__('SKRILL_FRONTEND_PM_ADB').'</span> <span class="adb_title" id="adb_bank_link">.'
                . '('. Mage::helper('skrill')->__('SKRILL_FRONTEND_SUPPORTED_BANK').
            ')</span><span class="adb_title" id="adb_bank_list"></span>';
    }
}