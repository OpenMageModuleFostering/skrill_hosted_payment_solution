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
 * Skrill helper
 *
 */
class Skrill_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Enterprise =================================

    protected $tokenUrlLive = 'https://ctpe.net/frontend/GenerateToken';
    protected $tokenUrlTest = 'https://test.ctpe.net/frontend/GenerateToken';

    protected $executeUrlLive = 'https://ctpe.net/frontend/ExecutePayment';
    protected $executeUrlTest = 'https://test.ctpe.net/frontend/ExecutePayment';

    protected $statusUrlLive = 'https://ctpe.net/frontend/GetStatus;jsessionid=';
    protected $statusUrlTest = 'https://test.ctpe.net/frontend/GetStatus;jsessionid=';

    protected $jsUrlLive = 'https://ctpe.net/frontend/widget/v3/widget.js?style=card&version=beautified&language=';
    protected $jsUrlTest = 'https://test.ctpe.net/frontend/widget/v3/widget.js?style=card&version=beautified&language=';

    public function getErrorIdentifier($code)
    {
        $error_messages = array(
            "40.10" => "ERROR_CC_DECLINED_CARD",
            "40.15" => "",
            "40.20" => "",
            "59.94" => "",
            "60.95" => "ERROR_CC_DECLINED_CARD",
            "60.70" => "ERROR_GENERAL_CANCEL",
            "60.71" => "",
            "60.40" => "ERROR_GENERAL_GENERAL",
            "60.80" => "",
            "60.90" => "ERROR_GENERAL_CANCEL",
            "64.78" => "",
            "65.78" => "ERROR_CC_DECLINED_CARD",
            "65.60" => "",
            "65.61" => "ERROR_CC_3DERROR",
            "65.75" => "",
            "65.77" => "",
            "65.79" => "",
            "65.80" => "ERROR_GENERAL_DECLINED_RISK",
            "65.85" => "ERROR_CC_3DERROR",
            "65.50" => "ERROR_GENERAL_BLACKLIST",
            "65.30" => "",
            "65.31" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "65.38" => "",
            "65.32" => "",
            "65.33" => "",
            "65.36" => "ERROR_CC_ACCOUNT",
            "65.35" => "ERROR_GENERAL_LIMIT_AMOUNT",
            "65.37" => "ERROR_CC_ADDRESS",
            "70.10" => "",
            "70.64" => "ERROR_SEPA_MANDATE",
            "70.70" => "",
            "70.30" => "",
            "70.20" => "ERROR_CC_3DAUTH",
            "70.60" => "",
            "70.61" => "",
            "70.40" => "ERROR_CC_INVALIDDATA",
            "70.35" => "",
            "70.80" => "",
            "70.21" => "ERROR_GENERAL_BLACKLIST",

            "800.150.100" => "ERROR_CC_ACCOUNT",

            "800.100.402" => "ERROR_CC_INVALIDDATA",
            "100.100.101" => "ERROR_CC_INVALIDDATA",
            "800.100.151" => "ERROR_CC_INVALIDDATA",
            "000.400.108" => "ERROR_CC_INVALIDDATA",
            "100.100.100" => "ERROR_CC_INVALIDDATA",
            "100.100.200" => "ERROR_CC_INVALIDDATA",
            "100.100.201" => "ERROR_CC_INVALIDDATA",
            "100.100.300" => "ERROR_CC_INVALIDDATA",
            "100.100.301" => "ERROR_CC_INVALIDDATA",
            "100.100.304" => "ERROR_CC_INVALIDDATA",
            "100.100.400" => "ERROR_CC_INVALIDDATA",
            "100.100.401" => "ERROR_CC_INVALIDDATA",
            "100.100.402" => "ERROR_CC_INVALIDDATA",
            "100.100.651" => "ERROR_CC_INVALIDDATA",
            "100.100.700" => "ERROR_CC_INVALIDDATA",
            "100.200.100" => "ERROR_CC_INVALIDDATA",
            "100.200.103" => "ERROR_CC_INVALIDDATA",
            "100.200.104" => "ERROR_CC_INVALIDDATA",
            "100.400.000" => "ERROR_CC_INVALIDDATA",
            "100.400.001" => "ERROR_CC_INVALIDDATA",
            "100.400.086" => "ERROR_CC_INVALIDDATA",
            "100.400.087" => "ERROR_CC_INVALIDDATA",
            "100.400.002" => "ERROR_CC_INVALIDDATA",
            "100.400.316" => "ERROR_CC_INVALIDDATA",
            "100.400.317" => "ERROR_CC_INVALIDDATA",
            "800.100.402" => "ERROR_CC_INVALIDDATA",

            "800.300.401" => "ERROR_CC_BLACKLIST",

            "800.100.171" => "ERROR_CC_DECLINED_CARD",
            "800.100.165" => "ERROR_CC_DECLINED_CARD",
            "800.100.159" => "ERROR_CC_DECLINED_CARD",
            "800.100.195" => "ERROR_CC_DECLINED_CARD",
            "000.400.101" => "ERROR_CC_DECLINED_CARD",
            "100.100.501" => "ERROR_CC_DECLINED_CARD",
            "100.100.701" => "ERROR_CC_DECLINED_CARD",
            "100.400.005" => "ERROR_CC_DECLINED_CARD",
            "100.400.020" => "ERROR_CC_DECLINED_CARD",
            "100.400.021" => "ERROR_CC_DECLINED_CARD",
            "100.400.030" => "ERROR_CC_DECLINED_CARD",
            "100.400.039" => "ERROR_CC_DECLINED_CARD",
            "100.400.081" => "ERROR_CC_DECLINED_CARD",
            "100.400.100" => "ERROR_CC_DECLINED_CARD",
            "100.400.123" => "ERROR_CC_DECLINED_CARD",
            "100.400.319" => "ERROR_CC_DECLINED_CARD",
            "800.100.154" => "ERROR_CC_DECLINED_CARD",
            "800.100.156" => "ERROR_CC_DECLINED_CARD",
            "800.100.158" => "ERROR_CC_DECLINED_CARD",
            "800.100.160" => "ERROR_CC_DECLINED_CARD",
            "800.100.161" => "ERROR_CC_DECLINED_CARD",
            "800.100.163" => "ERROR_CC_DECLINED_CARD",
            "800.100.164" => "ERROR_CC_DECLINED_CARD",
            "800.100.166" => "ERROR_CC_DECLINED_CARD",
            "800.100.167" => "ERROR_CC_DECLINED_CARD",
            "800.100.169" => "ERROR_CC_DECLINED_CARD",
            "800.100.170" => "ERROR_CC_DECLINED_CARD",
            "800.100.173" => "ERROR_CC_DECLINED_CARD",
            "800.100.174" => "ERROR_CC_DECLINED_CARD",
            "800.100.175" => "ERROR_CC_DECLINED_CARD",
            "800.100.176" => "ERROR_CC_DECLINED_CARD",
            "800.100.177" => "ERROR_CC_DECLINED_CARD",
            "800.100.190" => "ERROR_CC_DECLINED_CARD",
            "800.100.191" => "ERROR_CC_DECLINED_CARD",
            "800.100.196" => "ERROR_CC_DECLINED_CARD",
            "800.100.197" => "ERROR_CC_DECLINED_CARD",
            "800.100.168" => "ERROR_CC_DECLINED_CARD",

            "100.100.303" => "ERROR_CC_EXPIRED",

            "800.100.153" => "ERROR_CC_INVALIDCVV",
            "100.100.601" => "ERROR_CC_INVALIDCVV",
            "100.100.600" => "ERROR_CC_INVALIDCVV",
            "800.100.192" => "ERROR_CC_INVALIDCVV",

            "800.100.157" => "ERROR_CC_EXPIRY",

            "800.100.162" => "ERROR_CC_LIMIT_EXCEED",

            "100.400.040" => "ERROR_CC_3DAUTH",
            "100.400.060" => "ERROR_CC_3DAUTH",
            "100.400.080" => "ERROR_CC_3DAUTH",
            "100.400.120" => "ERROR_CC_3DAUTH",
            "100.400.260" => "ERROR_CC_3DAUTH",
            "800.900.300" => "ERROR_CC_3DAUTH",
            "800.900.301" => "ERROR_CC_3DAUTH",
            "800.900.302" => "ERROR_CC_3DAUTH",
            "100.380.401" => "ERROR_CC_3DAUTH",

            "100.390.105" => "ERROR_CC_3DERROR",
            "000.400.103" => "ERROR_CC_3DERROR",
            "000.400.104" => "ERROR_CC_3DERROR",
            "100.390.106" => "ERROR_CC_3DERROR",
            "100.390.107" => "ERROR_CC_3DERROR",
            "100.390.108" => "ERROR_CC_3DERROR",
            "100.390.109" => "ERROR_CC_3DERROR",
            "100.390.111" => "ERROR_CC_3DERROR",
            "800.400.200" => "ERROR_CC_3DERROR",
            "100.390.112" => "ERROR_CC_3DERROR",

            "100.100.500" => "ERROR_CC_NOBRAND",

            "800.100.155" => "ERROR_GENERAL_LIMIT_AMOUNT",
            "000.100.203" => "ERROR_GENERAL_LIMIT_AMOUNT",
            "100.550.310" => "ERROR_GENERAL_LIMIT_AMOUNT",
            "100.550.311" => "ERROR_GENERAL_LIMIT_AMOUNT",

            "800.120.101" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.100" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.102" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.103" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.200" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.201" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.202" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",
            "800.120.203" => "ERROR_GENERAL_LIMIT_TRANSACTIONS",

            "800.100.152" => "ERROR_CC_DECLINED_AUTH",

            "100.380.501" => "ERROR_GENERAL_DECLINED_RISK",

            "800.400.151" => "ERROR_CC_ADDRESS",
            "800.400.150" => "ERROR_CC_ADDRESS",

            "100.400.300" => "ERROR_GENERAL_CANCEL",
            "100.396.101" => "ERROR_GENERAL_CANCEL",
            "900.300.600" => "ERROR_GENERAL_CANCEL",

            "800.100.501" => "ERROR_CC_RECURRING",
            "800.100.500" => "ERROR_CC_RECURRING",

            "800.100.178" => "ERROR_CC_REPEATED",
            "800.300.500" => "ERROR_CC_REPEATED",
            "800.300.501" => "ERROR_CC_REPEATED",

            "800.700.101" => "ERROR_GENERAL_ADDRESS",
            "800.700.201" => "ERROR_GENERAL_ADDRESS",
            "800.700.500" => "ERROR_GENERAL_ADDRESS",
            "800.800.102" => "ERROR_GENERAL_ADDRESS",
            "800.800.202" => "ERROR_GENERAL_ADDRESS",
            "800.800.302" => "ERROR_GENERAL_ADDRESS",
            "800.900.101" => "ERROR_GENERAL_ADDRESS",
            "800.900.200" => "ERROR_GENERAL_ADDRESS",
            "800.100.198" => "ERROR_GENERAL_ADDRESS",
            "800.700.101" => "ERROR_GENERAL_ADDRESS",

            "100.400.121" => "ERROR_GENERAL_BLACKLIST",
            "800.100.172" => "ERROR_GENERAL_BLACKLIST",
            "800.200.159" => "ERROR_GENERAL_BLACKLIST",
            "800.200.160" => "ERROR_GENERAL_BLACKLIST",
            "800.200.165" => "ERROR_GENERAL_BLACKLIST",
            "800.200.202" => "ERROR_GENERAL_BLACKLIST",
            "800.200.208" => "ERROR_GENERAL_BLACKLIST",
            "800.200.220" => "ERROR_GENERAL_BLACKLIST",
            "800.300.101" => "ERROR_GENERAL_BLACKLIST",
            "800.300.102" => "ERROR_GENERAL_BLACKLIST",
            "800.300.200" => "ERROR_GENERAL_BLACKLIST",
            "800.300.301" => "ERROR_GENERAL_BLACKLIST",
            "800.300.302" => "ERROR_GENERAL_BLACKLIST",

            "800.500.100" => "ERROR_GENERAL_GENERAL",
            "800.700.100" => "ERROR_GENERAL_GENERAL",

            "000.100.203" => "ERROR_GENERAL_LIMIT_AMOUNT",
            "100.550.310" => "ERROR_GENERAL_LIMIT_AMOUNT",
            "100.550.311" => "ERROR_GENERAL_LIMIT_AMOUNT",

            "000.400.107" => "ERROR_GENERAL_TIMEOUT",
            "100.395.502" => "ERROR_GENERAL_TIMEOUT",

            "100.395.101" => "ERROR_GIRO_NOSUPPORT",
            "100.395.102" => "ERROR_GIRO_NOSUPPORT",

            "100.200.200" => "ERROR_SEPA_MANDATE",
            "000.100.204" => "ERROR_SEPA_MANDATE",
            "000.100.205" => "ERROR_SEPA_MANDATE"
        );
        if ($code)
            return array_key_exists($code, $error_messages) ? $error_messages[$code] : 'ERROR_UNKNOWN';
        else
            return 'ERROR_UNKNOWN';
    }

    public function getJsUrl($server, $lang){
        if ($server=="LIVE")
            return $this->jsUrlLive . $lang;
        else
            return $this->jsUrlTest . $lang;
    }

    public function getTokenUrl($server)
    {
        if ($server=="LIVE")
            return $this->tokenUrlLive;
        else
            return $this->tokenUrlTest;
    }

    public function getPostParameter($dataCust,$dataTransaction)
    {
        $data = "SECURITY.SENDER=" . $dataTransaction['sender'] .
                "&TRANSACTION.CHANNEL=" . $dataTransaction['channel_id'] .
                "&USER.LOGIN=" . $dataTransaction['login'] .
                "&USER.PWD=" . $dataTransaction['password'] .
                "&TRANSACTION.MODE=" . $dataTransaction['tx_mode'] .
                "&IDENTIFICATION.TRANSACTIONID=" . $dataTransaction['transId'].
                "&PAYMENT.TYPE=" . $dataTransaction['payment_type'] .
                "&PRESENTATION.AMOUNT=" . $dataCust['amount'] .
                "&PRESENTATION.CURRENCY=" . $dataCust['currency'] .
                "&ADDRESS.STREET=" . $dataCust['street'] .
                "&ADDRESS.ZIP=" . $dataCust['zip'] .
                "&ADDRESS.CITY=" . $dataCust['city'] .
                "&ADDRESS.COUNTRY=" . $dataCust['country_code'] .
                "&CONTACT.EMAIL=" . $dataCust['email'] .
                "&NAME.GIVEN=" . $dataCust['first_name'] .
                "&NAME.FAMILY=" . $dataCust['last_name'];

        return $data;
    }

    public function getToken($postData, $url)
    {
        $response = Mage::helper('skrill/curl')->sendRequest($url, $postData);

        $obj = json_decode($response);

        return $obj->{'transaction'}->{'token'};
    }

    public function getExecuteUrl($server)
    {
        if ($server=="LIVE")
            return $this->executeUrlLive;
        else
            return $this->executeUrlTest;
    }

    public function getPostExecutePayment($dataTransaction)
    {
        $data = "IDENTIFICATION.REFERENCEID=". $dataTransaction['refId'] ."&" .
                "PAYMENT.METHOD=". $dataTransaction['payment_method'] ."&" .
                "PAYMENT.TYPE=". $dataTransaction['payment_type'] ."&" .
                "PRESENTATION.AMOUNT=". $dataTransaction['amount'] ."&" .
                "PRESENTATION.CURRENCY=". $dataTransaction['currency'] ."&" .
                "SECURITY.SENDER=". $dataTransaction['sender'] ."&" .
                "TRANSACTION.CHANNEL=". $dataTransaction['channel_id'] ."&" .
                "TRANSACTION.MODE=" . $dataTransaction['tx_mode'] ."&" .
                "USER.LOGIN=". $dataTransaction['login'] ."&" .
                "USER.PWD=". $dataTransaction['password'];

        return $data;
    }

    public function executePayment($postData, $url)
    {
        $response = Mage::helper('skrill/curl')->sendRequest($url, $postData);

        return $response;
    }

    public function buildResponseArray($response)
    {
        $result = array();
        $entries = explode("&", $response);
        foreach ($entries as $entry) {
            $pair = explode("=", $entry);
            $result[$pair[0]] = urldecode($pair[1]);
        }
        return $result;
    }

    public function getStatusUrl($server, $token){
        if ($server=="LIVE")
            return $this->statusUrlLive . $token;
        else
            return $this->statusUrlTest . $token;
    }

    public function getPaymentStatus($url)
    {
        $response = Mage::helper('skrill/curl')->getResponse($url, true);

        return $response;
    }

    public function getPayonTrnStatus($code, $paymentType, $separatorType)
    {
        if ($paymentType == 'PA') {
            if ($code == 'ACK') {
                $status = Mage::helper('skrill')->__('BACKEND_TT_PREAUTH');
            } else {
                $status = Mage::helper('skrill')->__('BACKEND_TT_PREAUTH_FAILED');
            }
        } elseif ($paymentType == 'CP') {
            if ($code == 'ACK') {
                $status = Mage::helper('skrill')->__('BACKEND_TT_CAPTURED');
            } else {
                if ($separatorType != 'info') {
                    $status = Mage::helper('skrill')->__('BACKEND_TT_CAPTURED_FAILED');
                }
            }
        } elseif ($paymentType == 'RF') {
            if ($code == 'ACK') {
                $status = Mage::helper('skrill')->__('BACKEND_TT_REFUNDED');
            } else {
                if ($separatorType != 'info') {
                    $status = Mage::helper('skrill')->__('BACKEND_TT_REFUNDED_FAILED');
                }
            }
        } else {
            if ($code == 'ACK') {
                $status = Mage::helper('skrill')->__('BACKEND_TT_ACC');
            } else {
                $status = Mage::helper('skrill')->__('BACKEND_TT_FAILED');
            }
        }
        return $status;
    }

    protected function getpaymentMethodIdentifier($paymentBrand) {
        if ($paymentBrand) {
            if ($paymentBrand == 'VISA') {
                return "SKRILL_FRONTEND_PM_VSA";
            } elseif ($paymentBrand == 'MASTER') {
                return "SKRILL_FRONTEND_PM_MSC";
            } elseif ($paymentBrand == 'MAESTRO') {
                return "SKRILL_FRONTEND_PM_MAE";
            } elseif ($paymentBrand == 'AMEX') {
                return "SKRILL_FRONTEND_PM_AMX";
            } elseif ($paymentBrand == 'DIRECTDEBIT_SEPA_MIX_DE') {
                return "FRONTEND_PM_DD";
            } elseif ($paymentBrand == 'SOFORTUEBERWEISUNG') {
                return "FRONTEND_PM_SOFORT";
            } else {
                return "FRONTEND_PM_".$paymentBrand;
            }
        }
        return '';
    }

    public function getPayonComment($status, $paymentType, $paymentBrand, $bin, $separatorType, $type, $refundId, $refundStatus)
    {
        if ( $separatorType == "info" ) {
            $separator = "<br />";
        } else {
            $separator = ". ";
        }

        $comment = Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_STATUS')." : ".Mage::helper('skrill')->getPayonTrnStatus($status, $paymentType, $separatorType).$separator;

        $paymentMethodIdentifier = $this->getpaymentMethodIdentifier($paymentBrand);

        $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_PM')." : ".Mage::helper('skrill')->__($paymentMethodIdentifier).$separator;

        if ($bin) {
            $countryIso2 = Mage::helper('skrill')->getCountryIssuer($bin);
            if ($countryIso2) {
                $cardIssuer = Mage::app()->getLocale()->getCountryTranslation($countryIso2);
                $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_COUNTRY')." : ".$cardIssuer.$separator;
            }
        }
        if ($type == "fraud") {
            $comment = Mage::helper('skrill')->__('SKRILL_BACKEND_GENERAL_TRANSACTION')." ".Mage::helper('skrill')->__('BACKEND_GENERAL_FRAUD').$separator;
            $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_GENERAL_TRANSACTION_ID')." : ".$refundId.$separator;
            if ($status == 'ACK') {
                $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_STATUS')." : ".Mage::helper('skrill')->__('BACKEND_TT_'.$refundStatus).$separator;
            }
        }
        return $comment;
    }

    // Enterprise =================================

    // Skrill =====================================

    public function doQuery($action, $params)
    {
        $url = 'https://www.moneybookers.com/app/query.pl';

        $fields = $params;
        $fields['action'] = $action;
        $fields['email'] = Mage::getStoreConfig('payment/skrill_settings/merchant_account');
        $fields['password'] = Mage::getStoreConfig('payment/skrill_settings/api_passwd');

        $request = http_build_query($fields, '', '&');

        $response = Mage::helper('skrill/curl')->sendRequest($url, $request);

        return $response;
    }

    public function getStatusTrn($parameters)
    {
        // check status_trn 3 times if no response.
        for ($i=0; $i < 3; $i++) {
            $response = true;
            try {
                $result = $this->doQuery('status_trn', $parameters);
            } catch (Exception $e) {
                $response = false;
            }
            if ($response && $result)
            {
                return $this->getResponseArray($result);
            }
        }
        return false;
    }

    public function getResponseArray($strings)
    {
        $responseArray = array();
        $string = explode("\n",$strings);
        $responseArray['response_header'] = $string[0];
        if(!empty($string[1])) {
        $stringArr = explode("&",$string[1]);
            foreach ($stringArr as $key => $value) {
            $valueArr = explode("=",$value);
            $responseArray[urldecode($valueArr[0])] = urldecode($valueArr[1]);
            }
            return $responseArray;
        }
        else {
            return false;
        }
    }

    public function getTrnStatus($code)
    {
        switch ($code) {
            case '2':
                $status = Mage::helper('skrill')->__('BACKEND_TT_PROCESSED');
                break;
            case '0':
                $status = Mage::helper('skrill')->__('BACKEND_TT_PENDING');
                break;
            case '-1':
                $status = Mage::helper('skrill')->__('BACKEND_TT_CANCELLED');
                break;
            case '-2':
                $status = Mage::helper('skrill')->__('BACKEND_TT_FAILED');
                break;
            case '-3':
                $status = Mage::helper('skrill')->__('BACKEND_TT_CHARGEBACK');
                break;
            case '-4':
                $status = Mage::helper('skrill')->__('BACKEND_TT_REFUNDED');
                break;
            case '-5':
                $status = Mage::helper('skrill')->__('BACKEND_TT_REFUNDED_FAILED');
                break;
            case '-6':
                $status = Mage::helper('skrill')->__('BACKEND_TT_REFUNDED_PENDING');
                break;
            default:
                $status = Mage::helper('skrill')->__('ERROR_GENERAL_ABANDONED_BYUSER');
                break;
        }
        return $status;
    }

    public function getComment($response, $separatorType = false, $type = false)
    {
        if ( $separatorType == "info" )
            $separator = "<br />";
        else
            $separator = ". ";

        $comment = Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_STATUS')." : ".$this->getTrnStatus($response['status']).$separator;
        if (isset($response['payment_type']))
            $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_PM')." : ".Mage::helper('skrill')->__('SKRILL_FRONTEND_PM_'.$response['payment_type']).$separator;
        if (isset($response['payment_instrument_country']))
        {
            $countryIso2 = Mage::helper('skrill')->getCountryIso2($response['payment_instrument_country']);
            if ($countryIso2)
            {
                $cardIssuer = Mage::app()->getLocale()->getCountryTranslation($countryIso2);
                $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_COUNTRY')." : ".$cardIssuer.$separator;
            }
        }
        if ($type == "fraud")
        {
            $comment = Mage::helper('skrill')->__('SKRILL_BACKEND_GENERAL_TRANSACTION')." ".Mage::helper('skrill')->__('BACKEND_GENERAL_FRAUD').$separator;
            $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_GENERAL_TRANSACTION_ID')." : ".$response['mb_transaction_id'].$separator;
            $comment .= Mage::helper('skrill')->__('SKRILL_BACKEND_ORDER_STATUS')." : ".$this->getTrnStatus($response['status']).$separator;
        }
        if ($type == "refundStatus") {
            $comment .= Mage::helper('skrill')->__('BACKEND_TT_AMOUNT')." : ".$response['amount'].$separator;
        }
        return $comment;
    }

    public function doRefund($action, $params)
    {
        $url = 'https://www.moneybookers.com/app/refund.pl';

        if ($action == "prepare") {
            $fields = $params;
            $fields['action'] = $action;
            $fields['email'] = Mage::getStoreConfig('payment/skrill_settings/merchant_account');
            $fields['password'] = Mage::getStoreConfig('payment/skrill_settings/api_passwd');
        } elseif ($action == "refund") {
            $fields['action'] = $action;
            $fields['sid'] = $params;
        }

        $request = http_build_query($fields, '', '&');
        $response = Mage::helper('skrill/curl')->sendRequest($url, $request);

        return simplexml_load_string($response);
    }

    public function getCountryIso3($iso2)
    {
        $iso3 = array(
            "AF" => "AFG",
            "AL" => "ALB",
            "DZ" => "DZA",
            "AS" => "ASM",
            "AD" => "AND",
            "AO" => "AGO",
            "AI" => "AIA",
            "AQ" => "ATA",
            "AG" => "ATG",
            "AR" => "ARG",
            "AM" => "ARM",
            "AW" => "ABW",
            "AU" => "AUS",
            "AT" => "AUT",
            "AZ" => "AZE",
            "BS" => "BHS",
            "BH" => "BHR",
            "BD" => "BGD",
            "BB" => "BRB",
            "BY" => "BLR",
            "BE" => "BEL",
            "BZ" => "BLZ",
            "BJ" => "BEN",
            "BM" => "BMU",
            "BT" => "BTN",
            "BO" => "BOL",
            "BA" => "BIH",
            "BW" => "BWA",
            "BV" => "BVT",
            "BR" => "BRA",
            "IO" => "IOT",
            "VG" => "VGB",
            "BN" => "BRN",
            "BG" => "BGR",
            "BF" => "BFA",
            "BI" => "BDI",
            "KH" => "KHM",
            "CM" => "CMR",
            "CA" => "CAN",
            "CV" => "CPV",
            "KY" => "CYM",
            "CF" => "CAF",
            "TD" => "TCD",
            "CL" => "CHL",
            "CN" => "CHN",
            "CX" => "CXR",
            "CC" => "CCK",
            "CO" => "COL",
            "KM" => "COM",
            "CG" => "COG",
            "CD" => "COD",
            "CK" => "COK",
            "CR" => "CRI",
            "HR" => "HRV",
            "CU" => "CUB",
            "CY" => "CYP",
            "CZ" => "CZE",
            "CI" => "CIV",
            "DK" => "DNK",
            "DJ" => "DJI",
            "DM" => "DMA",
            "DO" => "DOM",
            "EC" => "ECU",
            "EG" => "EGY",
            "SV" => "SLV",
            "GQ" => "GNQ",
            "ER" => "ERI",
            "EE" => "EST",
            "ET" => "ETH",
            "FK" => "FLK",
            "FO" => "FRO",
            "FJ" => "FJI",
            "FI" => "FIN",
            "FR" => "FRA",
            "GF" => "GUF",
            "PF" => "PYF",
            "TF" => "ATF",
            "GA" => "GAB",
            "GM" => "GMB",
            "GE" => "GEO",
            "DE" => "DEU",
            "GH" => "GHA",
            "GI" => "GIB",
            "GR" => "GRC",
            "GL" => "GRL",
            "GD" => "GRD",
            "GP" => "GLD",
            "GU" => "GUM",
            "GT" => "GTM",
            "GG" => "GGY",
            "GN" => "HTI",
            "GW" => "HMD",
            "GY" => "VAT",
            "HT" => "GIN",
            "HM" => "GNB",
            "HN" => "HND",
            "HK" => "HKG",
            "HU" => "HUN",
            "IS" => "ISL",
            "IN" => "IND",
            "ID" => "IDN",
            "IR" => "IRN",
            "IQ" => "IRQ",
            "IE" => "IRL",
            "IM" => "IMN",
            "IL" => "ISR",
            "IT" => "ITA",
            "JM" => "JAM",
            "JP" => "JPN",
            "JE" => "JEY",
            "JO" => "JOR",
            "KZ" => "KAZ",
            "KE" => "KEN",
            "KI" => "KIR",
            "KW" => "KWT",
            "KG" => "KGZ",
            "LA" => "LAO",
            "LV" => "LVA",
            "LB" => "LBN",
            "LS" => "LSO",
            "LR" => "LBR",
            "LY" => "LBY",
            "LI" => "LIE",
            "LT" => "LTU",
            "LU" => "LUX",
            "MO" => "MAC",
            "MK" => "MKD",
            "MG" => "MDG",
            "MW" => "MWI",
            "MY" => "MYS",
            "MV" => "MDV",
            "ML" => "MLI",
            "MT" => "MLT",
            "MH" => "MHL",
            "MQ" => "MTQ",
            "MR" => "MRT",
            "MU" => "MUS",
            "YT" => "MYT",
            "MX" => "MEX",
            "FM" => "FSM",
            "MD" => "MDA",
            "MC" => "MCO",
            "MN" => "MNG",
            "ME" => "MNE",
            "MS" => "MSR",
            "MA" => "MAR",
            "MZ" => "MOZ",
            "MM" => "MMR",
            "NA" => "NAM",
            "NR" => "NRU",
            "NP" => "NPL",
            "NL" => "NLD",
            "AN" => "ANT",
            "NC" => "NCL",
            "NZ" => "NZL",
            "NI" => "NIC",
            "NE" => "NER",
            "NG" => "NGA",
            "NU" => "NIU",
            "NF" => "NFK",
            "KP" => "PRK",
            "MP" => "MNP",
            "NO" => "NOR",
            "OM" => "OMN",
            "PK" => "PAK",
            "PW" => "PLW",
            "PS" => "PSE",
            "PA" => "PAN",
            "PG" => "PNG",
            "PY" => "PRY",
            "PE" => "PER",
            "PH" => "PHL",
            "PN" => "PCN",
            "PL" => "POL",
            "PT" => "PRT",
            "PR" => "PRI",
            "QA" => "QAT",
            "RO" => "ROU",
            "RU" => "RUS",
            "RW" => "RWA",
            "RE" => "REU",
            "BL" => "BLM",
            "SH" => "SHN",
            "KN" => "KNA",
            "LC" => "LCA",
            "MF" => "MAF",
            "PM" => "SPM",
            "WS" => "WSM",
            "SM" => "SMR",
            "SA" => "SAU",
            "SN" => "SEN",
            "RS" => "SRB",
            "SC" => "SYC",
            "SL" => "SLE",
            "SG" => "SGP",
            "SK" => "SVK",
            "SI" => "SVN",
            "SB" => "SLB",
            "SO" => "SOM",
            "ZA" => "ZAF",
            "GS" => "SGS",
            "KR" => "KOR",
            "ES" => "ESP",
            "LK" => "LKA",
            "VC" => "VCT",
            "SD" => "SDN",
            "SR" => "SUR",
            "SJ" => "SJM",
            "SZ" => "SWZ",
            "SE" => "SWE",
            "CH" => "CHE",
            "SY" => "SYR",
            "ST" => "STP",
            "TW" => "TWN",
            "TJ" => "TJK",
            "TZ" => "TZA",
            "TH" => "THA",
            "TL" => "TLS",
            "TG" => "TGO",
            "TK" => "TKL",
            "TO" => "TON",
            "TT" => "TTO",
            "TN" => "TUN",
            "TR" => "TUR",
            "TM" => "TKM",
            "TC" => "TCA",
            "TV" => "TUV",
            "UM" => "UMI",
            "VI" => "VIR",
            "UG" => "UGA",
            "UA" => "UKR",
            "AE" => "ARE",
            "GB" => "GBR",
            "US" => "USA",
            "UY" => "URY",
            "UZ" => "UZB",
            "VU" => "VUT",
            "VA" => "GUY",
            "VE" => "VEN",
            "VN" => "VNM",
            "WF" => "WLF",
            "EH" => "ESH",
            "YE" => "YEM",
            "ZM" => "ZMB",
            "ZW" => "ZWE",
            "AX" => "ALA"
        );
        if ($iso2)
            return array_key_exists($iso2, $iso3) ? $iso3[$iso2] : '';
        else
            return '';
    }

    public function getCountryIso2($iso3)
    {
        $iso2 = array(
             "AFG" => "AF",
             "ALB" => "AL",
             "DZA" => "DZ",
             "ASM" => "AS",
             "AND" => "AD",
             "AGO" => "AO",
             "AIA" => "AI",
             "ATA" => "AQ",
             "ATG" => "AG",
             "ARG" => "AR",
             "ARM" => "AM",
             "ABW" => "AW",
             "AUS" => "AU",
             "AUT" => "AT",
             "AZE" => "AZ",
             "BHS" => "BS",
             "BHR" => "BH",
             "BGD" => "BD",
             "BRB" => "BB",
             "BLR" => "BY",
             "BEL" => "BE",
             "BLZ" => "BZ",
             "BEN" => "BJ",
             "BMU" => "BM",
             "BTN" => "BT",
             "BOL" => "BO",
             "BIH" => "BA",
             "BWA" => "BW",
             "BVT" => "BV",
             "BRA" => "BR",
             "IOT" => "IO",
             "VGB" => "VG",
             "BRN" => "BN",
             "BGR" => "BG",
             "BFA" => "BF",
             "BDI" => "BI",
             "KHM" => "KH",
             "CMR" => "CM",
             "CAN" => "CA",
             "CPV" => "CV",
             "CYM" => "KY",
             "CAF" => "CF",
             "TCD" => "TD",
             "CHL" => "CL",
             "CHN" => "CN",
             "CXR" => "CX",
             "CCK" => "CC",
             "COL" => "CO",
             "COM" => "KM",
             "COG" => "CG",
             "COD" => "CD",
             "COK" => "CK",
             "CRI" => "CR",
             "HRV" => "HR",
             "CUB" => "CU",
             "CYP" => "CY",
             "CZE" => "CZ",
             "CIV" => "CI",
             "DNK" => "DK",
             "DJI" => "DJ",
             "DMA" => "DM",
             "DOM" => "DO",
             "ECU" => "EC",
             "EGY" => "EG",
             "SLV" => "SV",
             "GNQ" => "GQ",
             "ERI" => "ER",
             "EST" => "EE",
             "ETH" => "ET",
             "FLK" => "FK",
             "FRO" => "FO",
             "FJI" => "FJ",
             "FIN" => "FI",
             "FRA" => "FR",
             "GUF" => "GF",
             "PYF" => "PF",
             "ATF" => "TF",
             "GAB" => "GA",
             "GMB" => "GM",
             "GEO" => "GE",
             "DEU" => "DE",
             "GHA" => "GH",
             "GIB" => "GI",
             "GRC" => "GR",
             "GRL" => "GL",
             "GRD" => "GD",
             "GLD" => "GP",
             "GUM" => "GU",
             "GTM" => "GT",
             "GGY" => "GG",
             "HTI" => "GN",
             "HMD" => "GW",
             "VAT" => "GY",
             "GIN" => "HT",
             "GNB" => "HM",
             "HND" => "HN",
             "HKG" => "HK",
             "HUN" => "HU",
             "ISL" => "IS",
             "IND" => "IN",
             "IDN" => "ID",
             "IRN" => "IR",
             "IRQ" => "IQ",
             "IRL" => "IE",
             "IMN" => "IM",
             "ISR" => "IL",
             "ITA" => "IT",
             "JAM" => "JM",
             "JPN" => "JP",
             "JEY" => "JE",
             "JOR" => "JO",
             "KAZ" => "KZ",
             "KEN" => "KE",
             "KIR" => "KI",
             "KWT" => "KW",
             "KGZ" => "KG",
             "LAO" => "LA",
             "LVA" => "LV",
             "LBN" => "LB",
             "LSO" => "LS",
             "LBR" => "LR",
             "LBY" => "LY",
             "LIE" => "LI",
             "LTU" => "LT",
             "LUX" => "LU",
             "MAC" => "MO",
             "MKD" => "MK",
             "MDG" => "MG",
             "MWI" => "MW",
             "MYS" => "MY",
             "MDV" => "MV",
             "MLI" => "ML",
             "MLT" => "MT",
             "MHL" => "MH",
             "MTQ" => "MQ",
             "MRT" => "MR",
             "MUS" => "MU",
             "MYT" => "YT",
             "MEX" => "MX",
             "FSM" => "FM",
             "MDA" => "MD",
             "MCO" => "MC",
             "MNG" => "MN",
             "MNE" => "ME",
             "MSR" => "MS",
             "MAR" => "MA",
             "MOZ" => "MZ",
             "MMR" => "MM",
             "NAM" => "NA",
             "NRU" => "NR",
             "NPL" => "NP",
             "NLD" => "NL",
             "ANT" => "AN",
             "NCL" => "NC",
             "NZL" => "NZ",
             "NIC" => "NI",
             "NER" => "NE",
             "NGA" => "NG",
             "NIU" => "NU",
             "NFK" => "NF",
             "PRK" => "KP",
             "MNP" => "MP",
             "NOR" => "NO",
             "OMN" => "OM",
             "PAK" => "PK",
             "PLW" => "PW",
             "PSE" => "PS",
             "PAN" => "PA",
             "PNG" => "PG",
             "PRY" => "PY",
             "PER" => "PE",
             "PHL" => "PH",
             "PCN" => "PN",
             "POL" => "PL",
             "PRT" => "PT",
             "PRI" => "PR",
             "QAT" => "QA",
             "ROU" => "RO",
             "RUS" => "RU",
             "RWA" => "RW",
             "REU" => "RE",
             "BLM" => "BL",
             "SHN" => "SH",
             "KNA" => "KN",
             "LCA" => "LC",
             "MAF" => "MF",
             "SPM" => "PM",
             "WSM" => "WS",
             "SMR" => "SM",
             "SAU" => "SA",
             "SEN" => "SN",
             "SRB" => "RS",
             "SYC" => "SC",
             "SLE" => "SL",
             "SGP" => "SG",
             "SVK" => "SK",
             "SVN" => "SI",
             "SLB" => "SB",
             "SOM" => "SO",
             "ZAF" => "ZA",
             "SGS" => "GS",
             "KOR" => "KR",
             "ESP" => "ES",
             "LKA" => "LK",
             "VCT" => "VC",
             "SDN" => "SD",
             "SUR" => "SR",
             "SJM" => "SJ",
             "SWZ" => "SZ",
             "SWE" => "SE",
             "CHE" => "CH",
             "SYR" => "SY",
             "STP" => "ST",
             "TWN" => "TW",
             "TJK" => "TJ",
             "TZA" => "TZ",
             "THA" => "TH",
             "TLS" => "TL",
             "TGO" => "TG",
             "TKL" => "TK",
             "TON" => "TO",
             "TTO" => "TT",
             "TUN" => "TN",
             "TUR" => "TR",
             "TKM" => "TM",
             "TCA" => "TC",
             "TUV" => "TV",
             "UMI" => "UM",
             "VIR" => "VI",
             "UGA" => "UG",
             "UKR" => "UA",
             "ARE" => "AE",
             "GBR" => "GB",
             "USA" => "US",
             "URY" => "UY",
             "UZB" => "UZ",
             "VUT" => "VU",
             "GUY" => "VA",
             "VEN" => "VE",
             "VNM" => "VN",
             "WLF" => "WF",
             "ESH" => "EH",
             "YEM" => "YE",
             "ZMB" => "ZM",
             "ZWE" => "ZW",
             "ALA" => "AX"        );
        if ($iso3)
            return array_key_exists($iso3, $iso2) ? $iso2[$iso3] : '';
        else
            return '';
    }

    public function getSkrillErrorMapping($code)
    {
        $error_messages = array(
            "01" => "SKRILL_ERROR_01",
            "02" => "SKRILL_ERROR_02",
            "03" => "SKRILL_ERROR_03",
            "04" => "SKRILL_ERROR_04",
            "05" => "SKRILL_ERROR_05",
            "08" => "SKRILL_ERROR_08",
            "09" => "SKRILL_ERROR_09",
            "10" => "SKRILL_ERROR_10",
            "12" => "SKRILL_ERROR_12",
            "15" => "SKRILL_ERROR_15",
            "19" => "SKRILL_ERROR_19",
            "24" => "SKRILL_ERROR_24",
            "28" => "SKRILL_ERROR_28",
            "32" => "SKRILL_ERROR_32",
            "37" => "SKRILL_ERROR_37",
            "38" => "SKRILL_ERROR_38",
            "42" => "SKRILL_ERROR_42",
            "44" => "SKRILL_ERROR_44",
            "51" => "SKRILL_ERROR_51",
            "63" => "SKRILL_ERROR_63",
            "70" => "SKRILL_ERROR_70",
            "71" => "SKRILL_ERROR_71",
            "80" => "SKRILL_ERROR_80",
            "98" => "SKRILL_ERROR_98",
            "99" => "SKRILL_ERROR_99_GENERAL"
        );
        if ($code)
            return array_key_exists($code, $error_messages) ? $error_messages[$code] : 'SKRILL_ERROR_99_GENERAL';
        else
            return 'SKRILL_ERROR_99_GENERAL';
    }

    // Skrill =====================================

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getModel('checkout/session')->getQuote();
    }


    /**
     * Retrieve order object
     *
     * @param int $id
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($id)
    {
        return Mage::getSingleton('sales/order')->load($id);
    }

    /**
     * Retrieve customer data
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getCustomerData(Mage_Core_Model_Abstract $order)
    {
        $data = array(
            'name_data'    => $this->getNameData($order),
            'address_data' => $this->getAddressData($order),
            'contact_data' => $this->getContactData($order)
        );

        return $data;
    }

    /**
     * Retrieve name data
     *
     * @param Mage_Core_Model_Abstract $order
     * @return array
     */
    public function getNameData(Mage_Core_Model_Abstract $order)
    {
        $dob = '';
        if(!is_null($order->getCustomerDob())) {
            $dob = new Zend_Date($order->getCustomerDob());
            $dob = $dob->toString("yyyy-MM-dd");
        }
        $data = array(
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name'  => $order->getBillingAddress()->getLastname(),
            'sex'        => $this->getGender($order),
            'dob'        => $dob,
            'company'    => $order->getBillingAddress()->getCompany(),
            'salutation' => $this->getPrefix($order)
        );

        return $data;
    }

    /**
     * Retrieve address data
     *
     * @param Mage_Core_Model_Abstract $order
     * @return array
     */
    public function getAddressData(Mage_Core_Model_Abstract $order)
    {
        $data = array(
            'country_code' => $order->getBillingAddress()->getCountryId(),
            'street'       => str_replace("\n", " ", $order->getBillingAddress()->getStreetFull()),
            'zip'          => $order->getBillingAddress()->getPostcode(),
            'city'         => $order->getBillingAddress()->getCity(),
            'state'        => $order->getBillingAddress()->getRegion(),
        );

        return $data;
    }

    /**
     * Retrieve contact data
     *
     * @param Mage_Core_Model_Abstract $order
     * @return array
     */
    public function getContactData(Mage_Core_Model_Abstract $order)
    {
        $data = array(
            'email' => $order->getCustomerEmail(),
            'phone' => $order->getBillingAddress()->getTelephone(),
            'ip'    => Mage::helper('core/http')->getRemoteAddr(false)
        );

        return $data;
    }

    /**
     * Retrieve Basket data
     *
     * @param Mage_Core_Model_Abstract $order || Mage_Sales_Model_Quote $order
     * @return array
     */
    public function getBasketData(Mage_Core_Model_Abstract $order)
    {
        if ( $order instanceof Mage_Core_Model_Abstract ) {
            $basket = array(
                'amount' => $order->getGrandTotal(),
                'currency' => $order->getOrderCurrencyCode(),
                'baseCurrency' => $order->getBaseCurrencyCode(),
                'baseAmount' => $order->getBaseGrandTotal()
            );
        }
        else if ( $order instanceof Mage_Sales_Model_Quote ) {
            $basket = array(
                'amount' => $order->getGrandTotal(),
                'currency' => $order->getQuoteCurrencyCode(),
                'baseCurrency' => $order->getBaseCurrencyCode(),
                'baseAmount' => $order->getBaseGrandTotal()
            );
        }

        return $basket;
    }

    /**
     * Retrieve method code for config loading
     *
     * @param Mage_Core_Model_Abstract $order
     * @return string
     */
    public function getMethodCode(Mage_Core_Model_Abstract $order)
    {
        return str_replace('skrill', 'method', $order->getPayment()->getMethod());
    }

    /**
     * This method returns the customer gender code
     *
     * @param Mage_Core_Model_Abstract $order
     * @return string
     */
    public function getGender(Mage_Core_Model_Abstract $order)
    {
        $gender = $order->getCustomerGender();
        if ($gender) {
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'gender');
            $option = $attribute->getFrontend()->getOption($gender);

            switch (strtolower($option)) {
                case 'male':
                    return 'M';
                case 'female':
                    return 'F';
            }
        }
        return '';
    }

    /**
     * Retrieve the prefix
     *
     * @param Mage_Core_Model_Abstract $order
     * @return string
     */
    public function getPrefix(Mage_Core_Model_Abstract $order)
    {
        $gender = $order->getCustomerPrefix();
        if ($gender) {
            switch (strtolower($gender)) {
                case 'herr':
                case 'mr':
                    return 'MR';
                case 'frau':
                case 'mrs':
                    return 'MRS';
                case 'fräulein':
                case 'ms':
                    return 'MS';

            }
        }
        return '';
    }

    /**
     * Retrieve the locale code in iso (2 chars)
     *
     * @return string
     */
    public function getLocaleIsoCode()
    {
        return substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
    }

    public function invoice(Mage_Core_Model_Abstract $order)
    {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();
    }

    public function getCountryIssuer($bin)
    {
        $url = 'http://www.binlist.net/json/'.$bin;

        $response = Mage::helper('skrill/curl')->getResponse($url, true);

        return $response['country_code'];
    }

    public function getDateTime()
    {
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

        return $d->format("ymdhiu");
    }

    public function randomNumber($length) {
        $result = '';

        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }

    public function getMerchantData($storeId)
    {
        $versionData['transaction_mode'] = 'LIVE';
        $versionData['ip_address'] = Mage::helper('core/http')->getServerAddr();
        $versionData['shop_version'] = Mage::getVersion();
        $versionData['plugin_version'] = Mage::getStoreConfig('payment/skrill_settings/version', $storeId);
        $versionData['client'] = 'Skrill';
        $versionData['merchant_id'] = Mage::getStoreConfig('payment/skrill_settings/merchant_id', $storeId);
        $versionData['shop_system'] = 'Magento';
        $versionData['shop_url'] = Mage::getStoreConfig('payment/skrill_settings/shop_url', $storeId);

        if (Mage::getStoreConfig('payment/skrill_settings/merchant_account', $storeId)) {
            $versionData['email'] = Mage::getStoreConfig('payment/skrill_settings/merchant_account', $storeId);
        } else {
            $collection = Mage::getModel('admin/user')->getCollection()->setPageSize(1)->getData();
            $versionData['email'] = $collection[0]['email'];
        }

        return $versionData;
    }

}

