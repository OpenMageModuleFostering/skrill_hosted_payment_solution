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

class Skrill_Model_Method_Ntl extends Skrill_Model_Method_Skrill
{

    /**
     * Path for payment form block
     *
     * @var string
     */
    protected $_formBlockType = 'skrill/payment_form_ntl';

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'skrill_ntl';

    /**
     *
     * @var string
     */
    protected $_accountBrand = 'NTL';

    /**
     * Payment Title
     *
     * @var type
     */
    protected $_methodTitle = 'SKRILL_FRONTEND_PM_NTL';
    
}
