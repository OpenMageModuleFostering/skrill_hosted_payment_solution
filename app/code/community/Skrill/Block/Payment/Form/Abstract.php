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

abstract class Skrill_Block_Payment_Form_Abstract extends Mage_Payment_Block_Form
{
    protected $logo_height = "35";

    protected function _construct()
    {
        $this->setMethodTitle('')
        ->setMethodLabelAfterHtml($this->_getLogoHtml());
    }

    protected function _getLogoHtml()
    {
        return sprintf(
            '<img src="%s" alt="%s"/ height="'.$this->logo_height.'px">',
            $this->_getLogoUrl(),
            $this->_getLogoAlt()
        );
    }

    /**
     * Retrieves the alt attribute for the logo
     *
     * @return  string
     */
    protected function _getLogoAlt()
    {
        return $this->_logoAlt;
    }

    /**
     * Retrieves the url to the logo
     *
     * @return  string
     */
    protected function _getLogoUrl()
    {
        return $this->_logoUrl;
    }

		
}
