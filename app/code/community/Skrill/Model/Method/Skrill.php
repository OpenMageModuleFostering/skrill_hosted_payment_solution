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
     * @var type
     */
    protected $_methodTitle = '';

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'skrill_abstract';

    /**
     * Redirect or iFrame
     * @var type 
     */
    
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
            'secret_word'        => Mage::getStoreConfig('payment/skrill_settings/secret_word', $this->getOrder()->getStoreId())
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

    public function getSid($fields)
    {
        $url = 'https://pay.skrill.com';

        foreach($fields as $key=>$value) { 
            $fields_string .= $key.'='.$value.'&'; 
        }
        $fields_string = rtrim($fields_string, '&');

        $curl = curl_init();

        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl,CURLOPT_POST, count($fields));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($curl);

        if(curl_errno($curl))
        {
            throw new Exception("Curl error: ". curl_error($curl));
        }

        curl_close($curl);

        return $result;

    }

    public function getPaymentMethods()
    {
        $payment_methods = "WLT,PSC,ACC,VSA,MSC,VSD,VSE,MAE,AMX,DIN,JCB,GCB,DNK,PSP,CSI,OBT,GIR,DID,SFT,EBT,IDL,NPY,PLI,PWY,EPY,GLU,ALI";
        $pm_list = explode(",", $payment_methods);
        //$list = $this->getAccountBrand().',';
        $list = '';
        foreach ($pm_list as $key => $pm) {
            // if ( $pm == $this->getAccountBrand() )
            //     continue;
            if ( Mage::getStoreConfig('payment/skrill_'.strtolower($pm).'/active') && Mage::getStoreConfig('payment/skrill_'.strtolower($pm).'/gateway') != "PAYON" )
                $list .= $pm.',';
        }

        return rtrim($list,",");
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
        $postParameters['transaction_id'] = Mage::getModel("sales/order")->getCollection()->getLastItem()->getIncrementId().Mage::helper('skrill')->getDateTime().Mage::helper('skrill')->randomNumber(4);
        $postParameters['return_url'] = Mage::getUrl('skrill/payment/handleResponse/',array('_secure'=>true));
        // $postParameters['status_url'] = Mage::getUrl('skrill/payment/handleStatus/',array('_secure'=>true));
        // $postParameters['status_url'] = "mailto: ";
        $postParameters['cancel_url'] = Mage::getUrl('checkout/onepage/',array('_secure'=>true));
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
        $postParameters['amount'] = $basket['amount'];
        $postParameters['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
        $postParameters['detail1_description'] = "Order pay from ".$contact['email'];
        $postParameters['Platform ID'] = '71422537';
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
        $invoice->setTransactionId($payment->getLastTransId());
        $invoice->save(); 
        $invoice->sendEmail();
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $params['mb_transaction_id'] = $payment->getAdditionalInformation('skrill_mb_transaction_id');
        $params['amount'] = $amount;

        $xml_result = Mage::helper('skrill')->doRefund('prepare', $params);

        $sid = (string) $xml_result->sid;

        $xml_result = Mage::helper('skrill')->doRefund('refund', $sid);

        $status = (string) $xml_result->status;
        $mb_trans_id = (string) $xml_result->mb_transaction_id;

        if ($status == '2') {    
            $payment->setAdditionalInformation('skrill_status', "-4");
            $payment->setTransactionId($mb_trans_id)
                    ->setIsTransactionClosed(1)->save();
        } else {
            $response['status'] = "-5";
            $comment = Mage::helper('skrill')->getComment($response);
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

