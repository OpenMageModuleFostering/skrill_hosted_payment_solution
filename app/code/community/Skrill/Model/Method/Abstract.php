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

// $ExternalLibPath=Mage::getModuleDir('', 'Skrill') . DS . 'core' . DS .'copyandpay.php';
// require_once ($ExternalLibPath);

abstract class Skrill_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{

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
     *
     * @var type
     */
    protected $_methodCode = '';

    /**
     * Payment Title
     *
     * @var type
     */
    protected $_methodTitle = '';

    /**
     * @var string
     */
    protected $_paymentType = 'DB';

    /**
     * Magento method code
     *
     * @var string
     */

    protected $_code = 'payon_abstract';
    protected $_skrillCode = 'skrill_abstract';

    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_infoBlockType = 'skrill/payment_payoninfo';

    public function __construct()
    {
        if ( Mage::getStoreConfig('payment/'.$this->_skrillCode.'/active') && Mage::getStoreConfig('payment/'.$this->_skrillCode.'/gateway') == "PAYON" )
            $this->_canUseCheckout = false;
        else
            $this->_canUseCheckout = false;
    }

    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        if ($field == "sort_order")
            $path = 'payment/'.$this->_skrillCode.'/'.$field;
        else
            $path = 'payment/'.$this->getCode().'/'.$field;

        return Mage::getStoreConfig($path, $storeId);
    }

    public function canUseForCountry($country)
    {
        if ( Mage::getStoreConfig('payment/'.$this->_skrillCode.'/allowspecific') == 1 ) {
            $availableCountries = explode(',', Mage::getStoreConfig('payment/'.$this->_skrillCode.'/specificcountry'));
            if(!in_array($country, $availableCountries)){
                return false;
            }

        }
        return true;
    }

    /**
     * Retrieve the server mode
     *
     * @return string
     */
    public function getServerMode()
    {
        $server_mode = Mage::getStoreConfig('payment/' . $this->_skrillCode . '/server_mode', $this->getOrder()->getStoreId());
        return $server_mode;
    }

    /**
     * Retrieve the credentials
     *
     * @return array
     */
    public function getCredentials()
    {
        $general = Mage::getStoreConfig('payment/skrill_general/active', $this->getOrder()->getStoreId());

        if ($general)
        {
            $credentials = array(
                'sender'      => Mage::getStoreConfig('payment/skrill_general/sender', $this->getOrder()->getStoreId()),
                'login'       => Mage::getStoreConfig('payment/skrill_general/login', $this->getOrder()->getStoreId()),
                'password'    => Mage::getStoreConfig('payment/skrill_general/password', $this->getOrder()->getStoreId())
            );
        }
        else
        {
            $credentials = array(
                'sender'      => Mage::getStoreConfig('payment/' . $this->_skrillCode . '/sender', $this->getOrder()->getStoreId()),
                'login'       => Mage::getStoreConfig('payment/' . $this->_skrillCode . '/login', $this->getOrder()->getStoreId()),
                'password'    => Mage::getStoreConfig('payment/' . $this->_skrillCode . '/password', $this->getOrder()->getStoreId())
            );
        }

        $credentials['channel_id'] = Mage::getStoreConfig('payment/' . $this->_skrillCode . '/channel_id', $this->getOrder()->getStoreId());

        return $credentials;
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

        $credentials = $this->getCredentials();
        $server = $this->getServerMode();

        Mage::getSingleton('customer/session')->setServerMode($server);

        $lang='';
        $jsUrl = Mage::helper('skrill')->getJsUrl($server,$lang);
        Mage::getSingleton('customer/session')->setJsUrl($jsUrl);

        $dataCust['first_name'] = $name['first_name'];
        $dataCust['last_name'] = $name['last_name'];
        $dataCust['street'] = $address['street'];
        $dataCust['zip'] = $address['zip'];
        $dataCust['city'] = $address['city'];
        $dataCust['country_code'] = $address['country_code'];
        $dataCust['email'] = $contact['email'];
        $dataCust['amount'] = $basket['amount'];
        $dataCust['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();

        $dataTransaction = $credentials;
        $dataTransaction['tx_mode'] = $this->getTransactionMode();
        $dataTransaction['payment_type'] = $this->getPaymentType();
        $dataTransaction['transId'] = $this->getOrderIncrementId().Mage::helper('skrill')->getDateTime().Mage::helper('skrill')->randomNumber(4);
        Mage::getSingleton('customer/session')->setDataTransaction($dataTransaction);

        $postData = Mage::helper('skrill')->getPostParameter($dataCust,$dataTransaction);

        $url = Mage::helper('skrill')->getTokenUrl($server);

        try {
            $token = Mage::helper('skrill')->getToken($postData,$url);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_REDIRECT'));
        }

        Mage::getSingleton('customer/session')->setIframeToken($token);
        Mage::getSingleton('customer/session')->setIframeName($name['first_name'].' '.$name['last_name']);
        Mage::getSingleton('customer/session')->setIframeBrand($this->_accountBrand);
        Mage::getSingleton('customer/session')->setIframeFrontendResponse(Mage::getUrl('skrill/response/handleCpResponse/',array('_secure'=>true)));

        if ($this->_code == "skrill_creditcard")
        {
            if ( !Mage::getStoreConfig('payment/'.$this->_skrillCode.'/card_selection') )
            {
                $brand = "AMEX VISA MASTER MAESTRO";
            }
            else
            {
                $brand = str_replace(",", " ",  Mage::getStoreConfig('payment/'.$this->_skrillCode.'/card_selection'));
            }
            Mage::getSingleton('customer/session')->setIframeCardBrand($brand);
        }

        if ($token) {
            $this->_paymentform();
        } else {
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_REDIRECT'));
        }

        return Mage::getSingleton('customer/session')->getRedirectUrl();
    }

    protected function getTransactionMode()
    {
        $server = $this->getServerMode();

        if ($server == "LIVE") {
            return 'LIVE';
        } else {
            switch ($this->_code) {
                case 'skrill_creditcard':
                case 'skrill_directdebit':
                case 'skrill_eps':
                case 'skrill_giropay':
                case 'skrill_yandex':
                    return 'INTEGRATOR_TEST';
                default:
            }
        }
    }

    public function capture(Varien_Object $payment, $amount)
    {

        if ($payment->getAdditionalInformation('skrill_transaction_code') == 'PA') {

            $order = $payment->getOrder();
            $base_currency = $order->getBaseCurrencyCode();
            $order_currency = $order->getOrderCurrencyCode();
            $convert_amount = Mage::helper('directory')->currencyConvert($amount, $base_currency, $order_currency);
            $convert_amount = round($convert_amount, 2, PHP_ROUND_HALF_DOWN);

            $refId = $payment->getAdditionalInformation('skrill_uniqueid');

            $dataTransaction =  $this->getCredentials();
            $dataTransaction['tx_mode'] = $this->getTransactionMode();

            $dataTransaction['refId'] = $refId;
            $dataTransaction['amount'] = $convert_amount;
            $dataTransaction['currency'] = $order_currency;
            $dataTransaction['payment_method'] = $this->_methodCode;
            $dataTransaction['payment_type'] = "CP";

            $postData = Mage::helper('skrill')->getPostExecutePayment($dataTransaction);

            $server = $this->getServerMode();

            $url = Mage::helper('skrill')->getExecuteUrl($server);

            try {
                $response = Mage::helper('skrill')->executePayment($postData, $url);
            } catch (Exception $e) {
                Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_PROCESSING'));
                return $this;
            }

            $result = Mage::helper('skrill')->buildResponseArray($response);

            if ($result['PROCESSING.RESULT'] == 'ACK') {
                $payment->setAdditionalInformation('skrill_status', "ACK");
                $payment->setAdditionalInformation('skrill_transaction_code', "CP");
                $payment->setStatus('APPROVED')
                        ->setTransactionId($payment->getAdditionalInformation('skrill_uniqueid'))
                        ->setIsTransactionClosed(1)->save();
            } else {
                $comment = Mage::helper('skrill')->getPayonComment($result['PROCESSING.RESULT'], "CP");
                $payment->getOrder()->addStatusHistoryComment($comment, false)->save();
                Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_PROCESSING'));
            }

        }
        else {
            $payment->setStatus('APPROVED')
                    ->setTransactionId($payment->getAdditionalInformation('skrill_uniqueid'))
                    ->setIsTransactionClosed(1)->save();
        }
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
        $order = $payment->getOrder();
        $base_currency = $order->getBaseCurrencyCode();
        $order_currency = $order->getOrderCurrencyCode();
        $convert_amount = Mage::helper('directory')->currencyConvert($amount, $base_currency, $order_currency);
        $convert_amount = round($convert_amount, 2, PHP_ROUND_HALF_DOWN);

        $refId = $payment->getAdditionalInformation('skrill_uniqueid');

        $dataTransaction =  $this->getCredentials();
        $dataTransaction['tx_mode'] = $this->getTransactionMode();

        $dataTransaction['refId'] = $refId;
        $dataTransaction['amount'] = $convert_amount;
        $dataTransaction['currency'] = $order_currency;
        $dataTransaction['payment_method'] = $this->_methodCode;
        $dataTransaction['payment_type'] = "RF";

        $postData = Mage::helper('skrill')->getPostExecutePayment($dataTransaction);

        $server = $this->getServerMode();

        $url = Mage::helper('skrill')->getExecuteUrl($server);

        try {
            $response = Mage::helper('skrill')->executePayment($postData, $url);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_PROCESSING'));
            return $this;
        }

        $result = Mage::helper('skrill')->buildResponseArray($response);

        if ($result['PROCESSING.RESULT'] == 'ACK') {
            $payment->setAdditionalInformation('skrill_status', "ACK");
            $payment->setAdditionalInformation('skrill_transaction_code', "RF");
            $payment->setTransactionId($result['IDENTIFICATION.UNIQUEID'])
                    ->setIsTransactionClosed(1)->save();
        } else {
            $comment = Mage::helper('skrill')->getPayonComment($result['PROCESSING.RESULT'], "RF");
            $payment->getOrder()->addStatusHistoryComment($comment, false)->save();
            Mage::throwException(Mage::helper('skrill')->__('ERROR_GENERAL_PROCESSING'));
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

    public function getPaymentType()
    {
        return $this->_paymentType;
    }

    /**
     *
     * @return string
     */
    public function getMethod()
    {
         return $this->_methodCode;
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


    protected function getActionUrl($action_name)
    {
        return Mage::app()->getStore(Mage::getDesign()->getStore())->getUrl('skrill/response/'.$action_name.'/', array('_secure'=>true));
    }
    /**
     * Set the iframe Url
     *
     * @param array $response
     */
    protected function _paymentform()
    {
        switch ($this->_code) {
            case 'skrill_creditcard':
                Mage::getSingleton('customer/session')->setRedirectUrl($this->getActionUrl("renderCC"));
                break;
            case 'skrill_directdebit':
                Mage::getSingleton('customer/session')->setRedirectUrl($this->getActionUrl("renderDD"));
                break;
            case 'skrill_paypal':
            case 'skrill_payolutioninv':
            case 'skrill_payolutionins':
            case 'skrill_paysafecard':
            case 'skrill_paytrail':
            case 'skrill_yandex':
                Mage::getSingleton('customer/session')->setRedirectUrl($this->getActionUrl("renderRedirect"));
                break;
            default:
                Mage::getSingleton('customer/session')->setRedirectUrl($this->getActionUrl("renderCP"));
                break;
        }
    }

}

