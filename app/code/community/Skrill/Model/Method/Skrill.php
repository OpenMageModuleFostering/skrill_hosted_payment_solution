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
 * Abstract payment model
 *
 */

abstract class Skrill_Model_Method_Skrill extends Mage_Payment_Model_Method_Abstract
{
    const PROCESSED_STATUS = '2';
    const PENDING_STATUS = '0';
    const FAILED_STATUS = '-2';
    const REFUNDED_STATUS = '-4';
    const REFUNDFAILED_STATUS = '-5';
    const REFUNDPENDING_STATUS = '-6';

    /**
     * Is method a gateaway
     *
     * @var boolean
     */
    protected $_isGateway = true;

    /**
     * Can this method use for checkout
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * Can this method use for multishipping
     *
     * @var boolean
     */
    protected $_canUseForMultishipping = false;

    /**
     * Is a initalize needed
     *
     * @var boolean
     */
    protected $_isInitializeNeeded = true;

    /**
     *
     * @var string
     */
    protected $_accountBrand = '';

    /**
     * Payment Title
     *
     * @var string
     */
    protected $_methodTitle = '';

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'skrill_abstract';

    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_infoBlockType = 'skrill/payment_skrillinfo';

    protected $_allowspecific = 0;

    public function __construct()
    {
        if ( Mage::getStoreConfig('payment/'.$this->getCode().'/show_separately') )
        {
            $this->_canUseCheckout = true;
            if ( Mage::getStoreConfig('payment/skrill_acc/active') && Mage::getStoreConfig('payment/skrill_acc/show_separately') )
            {
                switch ($this->getCode()) {
                    case 'skrill_vsa':
                    case 'skrill_msc':
                    case 'skrill_amx':
                    case 'skrill_din':
                    case 'skrill_jcb':
                        $this->_canUseCheckout = false;
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
        else
            $this->_canUseCheckout = false;

        $order = Mage::getSingleton('checkout/session')->getQuote();

        if ( $this->isCountryNotSupport($order) && $this->_canUseCheckout )
            $this->_canUseCheckout = false;
    }

    /**
     * Return Quote or Order Object depending what the Payment is
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            return $paymentInfo->getOrder();
        }

        return $paymentInfo->getQuote();
    }

    public function getOrderIncrementId()
    {
        $order = $this->getOrder();
        if ($order instanceof Mage_Sales_Model_Order) {
            return $order->getIncrementId();
        }
        return $order->getReservedOrderId();
    }

    /**
     * Retrieve the settings
     *
     * @return array
     */
    public function getSkrillSettings()
    {
        $settings = array(
            'merchant_id'  => Mage::getStoreConfig('payment/skrill_settings/merchant_id', $this->getOrder()->getStoreId()),
            'merchant_account'  => Mage::getStoreConfig('payment/skrill_settings/merchant_account', $this->getOrder()->getStoreId()),
            'recipient_desc'    => Mage::getStoreConfig('payment/skrill_settings/recipient_desc', $this->getOrder()->getStoreId()),
            'logo_url'          => urlencode(Mage::getStoreConfig('payment/skrill_settings/logo_url', $this->getOrder()->getStoreId())),
            'api_passwd'        => Mage::getStoreConfig('payment/skrill_settings/api_passwd', $this->getOrder()->getStoreId()),
            'secret_word'        => Mage::getStoreConfig('payment/skrill_settings/secret_word', $this->getOrder()->getStoreId()),
            'merchant_email'        => Mage::getStoreConfig('payment/skrill_settings/merchant_email', $this->getOrder()->getStoreId())
        );

        return $settings;
    }

    public function getDisplay()
    {
        return Mage::getStoreConfig('payment/skrill_settings/display', $this->getOrder()->getStoreId());
    }

    public function isEU($order)
    {
        $countryId = $order->getBillingAddress()->getCountryId();
        $eu_countries = Mage::getStoreConfig('general/country/eu_countries');
        $eu_countries_array = explode(',',$eu_countries);
        if(in_array($countryId, $eu_countries_array))
            return true;
        else
            return false;
    }

    public function canUseForPayOn()
    {
        $listPayment = array('skrill_acc', 'skrill_did', 'skrill_npy', 'skrill_gir', 'skrill_idl', 'skrill_sft', 'skrill_psc' );
        if ( in_array($this->_code, $listPayment) )
            return true;
        else
            return false;
    }

    public function canUseForCountry($country)
    {
        if ($this->canUseForPayOn())
        {
            if($this->_allowspecific == 1){
                $availableCountries = explode(',', $this->_specificcountry);
                if(!in_array($country, $availableCountries)){
                    return false;
                }

            }
            return true;
        }
        else
        {
            return parent::canUseForCountry($country);
        }
    }

    public function isCountryNotSupport($order)
    {
        $countryId = $order->getBillingAddress()->getCountryId();
        $not_support_countries = "AF,MM,NG,KP,SD,SY,SO,YE";
        $not_support_countries_array = explode(',',$not_support_countries);
        if(in_array($countryId, $not_support_countries_array))
            return true;
        else
            return false;
    }

    public function getSid($parameters)
    {
        $url = 'https://pay.skrill.com';

        $request = http_build_query($parameters, '', '&');

        $response = Mage::helper('skrill/curl')->sendRequest($url, $request);

        Mage::log('get sid request', null, 'skrill_log_file.log');
        Mage::log($parameters, null, 'skrill_log_file.log');
        Mage::log('get sid response : '.$response, null, 'skrill_log_file.log');

        return $response;
    }

    public function getPaymentMethods()
    {
        $payment_methods = "WLT,PSC,ACC,VSA,MSC,VSD,VSE,MAE,AMX,DIN,JCB,GCB,DNK,PSP,CSI,OBT,GIR,DID,SFT,EBT,IDL,NPY,PLI,PWY,EPY,GLU,ALI,NTL";
        $pm_list = explode(",", $payment_methods);
        $list = '';
        foreach ($pm_list as $key => $pm) {
            if ( Mage::getStoreConfig('payment/skrill_'.strtolower($pm).'/active') && Mage::getStoreConfig('payment/skrill_'.strtolower($pm).'/gateway') != "PAYON" )
                $list .= $pm.',';
        }

        return rtrim($list,",");
    }

    protected function getStatusUrl()
    {
        $statusUrl = Mage::getUrl(
            'skrill/payment/handleStatusResponse/',
            array(
                'orderId' => $this->getOrderIncrementId(),
                'paymentMethod' => $this->getCode(),
                '_secure' => true
            )
        );

        return $statusUrl;
    }

    protected function getRefundStatusUrl($orderId)
    {
        $refundStatusUrl = Mage::getUrl(
            'skrill/payment/handleRefundStatusResponse/',
            array(
                'orderId' => $orderId,
                'paymentMethod' => $this->getCode(),
                '_secure' => true
            )
        );

        return $refundStatusUrl;
    }

    /**
     * Retrieve the order place URL
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $name = Mage::helper('skrill')->getNameData($this->getOrder());
        $address = Mage::helper('skrill')->getAddressData($this->getOrder());
        $contact = Mage::helper('skrill')->getContactData($this->getOrder());
        $basket = Mage::helper('skrill')->getBasketData($this->getOrder());
        $settings = $this->getSkrillSettings();

        if (empty($settings['merchant_id']) || empty($settings['merchant_account']) || empty($settings['api_passwd']) || empty($settings['secret_word']))
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_REDIRECT'));

        $postParameters['pay_to_email'] = $settings['merchant_account'];
        $postParameters['recipient_description'] = $settings['recipient_desc'];
        $postParameters['transaction_id'] = $this->getOrderIncrementId().Mage::helper('skrill')->getDateTime().Mage::helper('skrill')->randomNumber(4);
        $postParameters['return_url'] = Mage::getUrl(
            'skrill/payment/handleResponse/',
            array('_secure' => true)
        )
        ."?orderId=".urlencode($this->getOrderIncrementId())
        ."&paymentMethod=".$this->getCode();
        $postParameters['status_url'] = $this->getStatusUrl();
        if (isset($settings['merchant_email'])) {
            $postParameters['status_url2'] = $settings['merchant_email'];
        }
        $postParameters['cancel_url'] = Mage::getUrl('skrill/payment/cancelResponse',array('_secure'=>true));
        $postParameters['language'] = $this->getLanguage();
        $postParameters['logo_url'] = $settings['logo_url'];
        $postParameters['prepare_only'] = 1;
        $postParameters['pay_from_email'] = $contact['email'];
        $postParameters['firstname'] = $name['first_name'];
        $postParameters['lastname'] = $name['last_name'];
        $postParameters['address'] = $address['street'];
        $postParameters['postal_code'] = $address['zip'];
        $postParameters['city'] = $address['city'];
        $postParameters['country'] = Mage::helper('skrill')->getCountryIso3($address['country_code']);
        $postParameters['amount'] = $basket['baseAmount'];
        $postParameters['currency'] = $basket['baseCurrency'];
        $postParameters['detail1_description'] = "Order pay from ".$contact['email'];
        $postParameters['merchant_fields'] = 'Platform';
        $postParameters['Platform'] = '71422537';

        if ($this->_code != "skrill_flexible")
            $postParameters['payment_methods'] = $this->getAccountBrand();

        try {
            $sid = $this->getSid($postParameters);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_REDIRECT'));
        }

        if (!$sid)
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_REDIRECT'));

        Mage::getSingleton('customer/session')->setRedirectUrl('https://pay.skrill.com/?sid='.$sid);

        if ($this->getDisplay() == "IFRAME" )
            return Mage::app()->getStore(Mage::getDesign()->getStore())->getUrl('skrill/payment/qcheckout/', array('_secure'=>true));
        else
            return Mage::getSingleton('customer/session')->getRedirectUrl();

    }

    public function getLanguage(){

        $langs = Mage::helper('skrill')->getLocaleIsoCode();
        switch ($langs) {
            case 'de':
              $lang = 'DE';
              break;

            default:
              $lang='EN';
        }

        return $lang;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus('APPROVED')
                ->setTransactionId($payment->getAdditionalInformation('skrill_mb_transaction_id'))
                ->setIsTransactionClosed(1)->save();

        return $this;
    }

    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId())->save();
        $invoice->sendEmail();
        $payment->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_accepted')->save();
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        if ($payment->getAdditionalInformation('skrill_refund_status') == self::REFUNDPENDING_STATUS) {
            Mage::throwException(Mage::helper('skrill')->__('BACKEND_GENERAL_WAIT_REFUND_PENDING'));
        }

        $orderId = $payment->getOrder()->getIncrementId();
        $params['mb_transaction_id'] = $payment->getAdditionalInformation('skrill_mb_transaction_id');
        $params['amount'] = $amount;
        $params['refund_status_url'] = $this->getRefundStatusUrl($orderId);

        Mage::log('refund prepare request', null, 'skrill_log_file.log');
        Mage::log($params, null, 'skrill_log_file.log');

        $xml_result = Mage::helper('skrill')->doRefund('prepare', $params);

        Mage::log('refund prepare response : '.Zend_Debug::dump($xml_result, null, false), null, 'skrill_log_file.log');

        $sid = (string) $xml_result->sid;

        $xml_result = Mage::helper('skrill')->doRefund('refund', $sid);

        Mage::log('refund response with sid '.$sid.' : '.Zend_Debug::dump($xml_result, null, false), null, 'skrill_log_file.log');

        $status = (string) $xml_result->status;
        $mb_trans_id = (string) $xml_result->mb_transaction_id;

        if ($status == self::PROCESSED_STATUS) {
            $payment->setAdditionalInformation('skrill_refund_status', self::REFUNDED_STATUS);
            $payment->setTransactionId($mb_trans_id)
                    ->setIsTransactionClosed(1)->save();
            $response['status'] = self::REFUNDED_STATUS;
            $comment = Mage::helper('skrill')->getComment($response);
            $payment->getOrder()->addStatusHistoryComment($comment, false)->save();
        } elseif ($status == self::PENDING_STATUS) {
            $payment->setAdditionalInformation('skrill_refund_status', self::REFUNDPENDING_STATUS)->save();
            $response['status'] = self::REFUNDPENDING_STATUS;
            $comment = Mage::helper('skrill')->getComment($response);
            $payment->getOrder()->addStatusHistoryComment($comment, false)->save();
            Mage::throwException(Mage::helper('skrill')->__('SUCCESS_GENERAL_REFUND_PAYMENT_PENDING'));
        } else {
            $payment->setAdditionalInformation('skrill_refund_status', self::REFUNDFAILED_STATUS)->save();
            $response['status'] = self::REFUNDFAILED_STATUS;
            $comment = Mage::helper('skrill')->getComment($response);
            $payment->getOrder()->addStatusHistoryComment($comment, false)->save();
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_REFUND_PAYMENT'));
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAccountBrand()
    {
        return $this->_accountBrand;
    }

    /**
     * Returns Payment Title
     *
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('skrill')->__($this->_methodTitle);
    }

}

