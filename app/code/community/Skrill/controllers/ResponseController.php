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
 * Response controller
 *
 */

class Skrill_ResponseController extends Mage_Core_Controller_Front_Action
{
   protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function handleCpResponseAction()
    {
        $session = $this->_getCheckout();

        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        if (!$order->getId()) {
            Mage::throwException('No order for processing found');
        }

        $this->_getPostResponseActionUrl($order);
    }

    protected function _checkStatusPayment($url, &$resultJson)
    {
        // check get status payment 3 times if no response.
        for ($i=0; $i < 3; $i++) {
            $no_response = false;
            try {
                $resultJson = Mage::helper('skrill')->getPaymentStatus($url);
            } catch (Exception $e) {
                $no_response = true;
            }
            if (!$no_response && $resultJson)
            {
                return false;
            }
        }
        return true;
    }

    protected function _checkFraud($resultJson)
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $quoteData = $quote->getData();
        $grandTotal = (float) $quoteData['grand_total'];
        $amount = (float) $resultJson['transaction']['payment']['clearing']['amount'];
        if ($resultJson['transaction']['payment']['clearing']['amount'])
            return $grandTotal != $amount;
        else
            return false;
    }

    protected function _redirectError($returnMessage, $url='checkout/onepage')
    {
        Mage::getSingleton('core/session')->addError($returnMessage);
        $this->_redirect($url, array('_secure'=>true));
    }

    protected function _doRefund($resultJson)
    {
        $dataTransaction = Mage::getSingleton('customer/session')->getDataTransaction();
        $dataTransaction['refId'] = $resultJson['transaction']['identification']['uniqueId'];
        $dataTransaction['amount'] = $resultJson['transaction']['payment']['clearing']['amount'];
        $dataTransaction['currency'] = $resultJson['transaction']['payment']['clearing']['currency'];
        $payment_method = substr($resultJson['transaction']['payment']['code'],0,2);
        $dataTransaction['payment_method'] = $payment_method;
        $payment_type = substr($resultJson['transaction']['payment']['code'],-2);
        if ($payment_type == 'PA')
            $dataTransaction['payment_type'] = "RV";
        else
            $dataTransaction['payment_type'] = "RF";

        $postData = Mage::helper('skrill')->getPostExecutePayment($dataTransaction);
        $server = Mage::getSingleton('customer/session')->getServerMode();

        $url = Mage::helper('skrill')->getExecuteUrl($server);

        try {
            $response = Mage::helper('skrill')->executePayment($postData, $url);
        } catch (Exception $e) {
            return false;
        }

        $result = Mage::helper('skrill')->buildResponseArray($response);
        if ($result['PROCESSING.RESULT'] == 'ACK')
            return true;
        else
            return false;
    }

    protected function _getPostResponseActionUrl(Mage_Sales_Model_Order $order)
    {
        $token = $this->getRequest()->getParam('token');
        $server = Mage::getSingleton('customer/session')->getServerMode();
        $url = Mage::helper('skrill')->getStatusUrl($server, $token);

        $no_response = $this->_checkStatusPayment($url, $resultJson);

        if ($no_response)
        {
            $comment = Mage::helper('skrill')->getPayonComment('NOK');
            $order->addStatusHistoryComment($comment, false);
            $order->save();
            $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_NORESPONSE'));
        }
        else
        {
            $isFraud = false;
            $theResult = $resultJson['transaction']['processing']['result'];
            $returnCode = $resultJson['transaction']['processing']['return']['code'];
            $returnMessage = Mage::helper('skrill')->__(Mage::helper('skrill')->getErrorIdentifier($returnCode));

            $currency = $resultJson['transaction']['payment']['clearing']['currency'];
            $payment_type = substr($resultJson['transaction']['payment']['code'],-2);

            $uniqueId = $resultJson['transaction']['identification']['uniqueId'];
            $payment_brand = $resultJson['transaction']['account']['brand'];
            $ip_country = $resultJson['transaction']['customer']['contact']['ipCountry'];
            $bin = $resultJson['transaction']['account']['bin'];

            if ($theResult == 'ACK') {
                if ($isFraud)
                {
                    $refunded = $this->_doRefund($resultJson);
                    if ($refunded)
                        $refund_status = 'REFUNDED';
                    else
                        $refund_status = 'REFUNDED_FAILED';

                    $comment = Mage::helper('skrill')->getPayonComment($theResult, $payment_type, $payment_brand, $bin, 'history', 'fraud', $uniqueId, $refund_status);
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();
                    $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_FRAUD_DETECTION'));
                }
                else
                {
                    $order->getPayment()->setAdditionalInformation('skrill_status', $theResult);
                    $order->getPayment()->setAdditionalInformation('skrill_uniqueid',$uniqueId);
                    $order->getPayment()->setAdditionalInformation('skrill_currency',$currency);
                    $order->getPayment()->setAdditionalInformation('skrill_transaction_code',$payment_type);

                    $order->getPayment()->setAdditionalInformation('skrill_ip_country', strtoupper($ip_country));
                    $order->getPayment()->setAdditionalInformation('skrill_payment_brand', $payment_brand);
                    $order->getPayment()->setAdditionalInformation('skrill_bin', $bin);

                    if ($payment_type == 'PA') {
                        $order->setState(Mage_Sales_Model_Order::STATE_NEW, true)->save();
                    } else {
                        Mage::helper('skrill')->invoice($order);
                    }

                    $comment = Mage::helper('skrill')->getPayonComment($theResult, $payment_type, $payment_brand, $bin);
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();
                    $order->sendNewOrderEmail();

                    Mage::getModel('sales/quote')->load($order->getQuoteId())->setIsActive(false)->save();
                    $this->_redirect('checkout/onepage/success', array('_secure'=>true));
                }
            } else if ($theResult == 'NOK') {
                if ($isFraud)
                {
                    $comment = Mage::helper('skrill')->getPayonComment($theResult, $payment_type, $payment_brand, $bin, 'history', 'fraud', $uniqueId);
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();
                    $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_FRAUD_DETECTION'));
                }
                else
                {
                    $comment = Mage::helper('skrill')->getPayonComment($theResult, $payment_type, $payment_brand, $bin);
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();
                    $this->_redirectError($returnMessage);
                }
            } else {
                if ($isFraud)
                {
                    $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_FRAUD_DETECTION'));
                }
                else
                {
                    $this->_redirectError(Mage::helper('skrill')->__('ERROR_UNKNOWN'));
                }
            }
        }
    }

    /**
     * Render the Payment Form page
     */
    public function renderCCAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('skrill/payment_formcc');

        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function renderDDAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('skrill/payment_formdd');

        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function renderRedirectAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('skrill/payment/formcp.phtml');
        $this->renderLayout();
    }

    public function renderCPAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('skrill/payment_formcp');

        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function myurlencode($val)
    {
        return urlencode(str_replace("/", "%2f", $val));
    }

    public function myurldecode($val)
    {
        return str_replace("%2f", "/", urldecode($val));
    }

}