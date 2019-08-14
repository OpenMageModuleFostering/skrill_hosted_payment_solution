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

class Skrill_PaymentController extends Mage_Core_Controller_Front_Action
{

    protected $processedStatus = '2';
    protected $pendingStatus = '0';
    protected $failedStatus = '-2';
    protected $refundedStatus = '-4';
    protected $refundFailedStatus = '-5';

    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Render the Payment Form page
     */

    public function qcheckoutAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('skrill/payment_qcheckout');

        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function handleResponseAction()
    {
        $orderId = urldecode($this->getRequest()->getParam('orderId'));
        
        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId($orderId);
        
        if (!$order->getId())
            Mage::throwException('No order for processing found');
        
        $this->processRedirectResponse($order);
    }

    public function cancelResponseAction(){
        $session = Mage::getSingleton('checkout/session');
        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());

        if (!$order->getId())
            Mage::throwException('No order for processing found');

        $order->cancel();
        $order->save();
        $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_CANCEL'));
    }

    protected function processRedirectResponse($order)
    {
        $payment = $order->getPayment();

        $statusUrlResponse = $payment->getAdditionalInformation('skrill_status_url_response');
        if (isset($statusUrlResponse)) {
            $responseStatus = unserialize($statusUrlResponse);
        } else {
            $parameters['trn_id'] = $this->getRequest()->getParam('transaction_id');
            $responseStatus = Mage::helper('skrill')->getStatusTrn($parameters);
        }

        if ($responseStatus) {
            $this->validatePayment($order, $responseStatus);
        } else {
            $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_NORESPONSE'));
        }

        if ($payment->getAdditionalInformation('skrill_status') == $this->processedStatus) {
            $this->_redirect('checkout/onepage/success');
        } elseif ($payment->getAdditionalInformation('skrill_status') == $this->failedStatus) {
            $failedReasonCode = $payment->getAdditionalInformation('failed_reason_code');
            $this->_redirectError(Mage::helper('skrill')->__(Mage::helper('skrill')->getSkrillErrorMapping($failedReasonCode)));
        } elseif ($payment->getAdditionalInformation('skrill_status') == $this->refundedStatus
            || $payment->getAdditionalInformation('skrill_status') == $this->refundFailedStatus) {
            $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_FRAUD_DETECTION'));
        } else {
            $this->_redirectError(Mage::helper('skrill')->__('SKRILL_ERROR_99_GENERAL'));
        }
    }

    public function handleStatusResponseAction()
    {
        $status = $this->getRequest()->getParam('status');

        if (isset($status)) {
            $orderId = $this->getRequest()->getParam('orderId');

            $order = Mage::getSingleton('sales/order');
            $order->loadByIncrementId($orderId);

            $order->getPayment()->setAdditionalInformation('skrill_status_url_response', serialize($this->getResponseStatus()));
            $order->getPayment()->save();
        }
    }

    protected function validatePayment($order, $responseStatus)
    {
        if ($responseStatus['payment_type'] == 'NGP') {
            $responseStatus['payment_type'] = 'OBT';
        }

        $versionData = Mage::helper('skrill')->getMerchantData($order->getStoreId());
        Mage::helper('skrill/versionTracker')->sendVersionTracker($versionData);

        $this->saveAdditionalInformation($order, $responseStatus);

        $isFraud = $this->isFraud($order, $responseStatus);
        if ($isFraud) {
            $this->processFraud($order, $responseStatus);
        } else {
            $this->processPayment($order, $responseStatus);
        }

    }

    protected function getResponseStatus()
    {
        $responseStatus = array();
        foreach ($_REQUEST as $responseName => $responseValue) {
            $responseStatus[strtolower($responseName)] = $responseValue;
        }
        return $responseStatus;
    }

    protected function saveAdditionalInformation($order, $responseStatus)
    {
        if(isset($responseStatus['transaction_id'])) {
            $order->getPayment()->setAdditionalInformation('skrill_transaction_id', $responseStatus['transaction_id']); }
        if(isset($responseStatus['mb_transaction_id'])) {
            $order->getPayment()->setAdditionalInformation('skrill_mb_transaction_id', $responseStatus['mb_transaction_id']); }
        if(isset($responseStatus['ip_country'])) {    
            $order->getPayment()->setAdditionalInformation('skrill_ip_country', $responseStatus['ip_country']); }
        if(isset($responseStatus['status'])) {
            $order->getPayment()->setAdditionalInformation('skrill_status', $responseStatus['status']); }
        if(isset($responseStatus['payment_type'])) {
            $order->getPayment()->setAdditionalInformation('skrill_payment_type', $responseStatus['payment_type']); }
        if(isset($responseStatus['payment_instrument_country'])) {    
            $order->getPayment()->setAdditionalInformation('skrill_issuer_country', $responseStatus['payment_instrument_country']); }
        if(isset($responseStatus['currency'])) {
            $order->getPayment()->setAdditionalInformation('skrill_currency', $responseStatus['currency']); }
        $order->getPayment()->save();
    }

    protected function setNumberFormat($number)
    {
        $number = (float) str_replace(',','.',$number);
        return number_format($number, 2, '.', '');
    }

    protected function isFraud($order, $response)
    {
        if ($response['amount']) {
            return !($response['md5sig'] == $this->generateMd5sig($order, $response));
        } else {
            return false;
        }
    }

    protected function generateMd5sig($order, $response)
    {
        $string = Mage::getStoreConfig('payment/skrill_settings/merchant_id', $order->getStoreId()).$response['transaction_id'].strtoupper(Mage::getStoreConfig('payment/skrill_settings/secret_word', $order->getStoreId())).$response['mb_amount'].$response['mb_currency'].$response['status'];

        return strtoupper(md5($string));
    }

    protected function processFraud($order, $responseStatus)
    {
        $comment = Mage::helper('skrill')->getComment($responseStatus);
        $order->addStatusHistoryComment($comment, false);
        $order->save();

        $params['mb_transaction_id'] = $responseStatus['mb_transaction_id'];
        $params['amount'] = $responseStatus['mb_amount'];

        $xmlResult = Mage::helper('skrill')->doRefund('prepare', $params);
        $sid = (string) $xmlResult->sid;
        $xmlResult = Mage::helper('skrill')->doRefund('refund', $sid);

        $status = (string) $xmlResult->status;
        $mbTransactionId = (string) $xmlResult->mb_transaction_id;

        if ($status == $this->processedStatus) {
            $responseStatus['status'] = $this->refundedStatus;
            $order->getPayment()->setAdditionalInformation('skrill_status', $responseStatus['status']);
            $order->getPayment()->setTransactionId($mbTransactionId)
                    ->setIsTransactionClosed(1)->save();
        } else {
            $responseStatus['status'] = $this->refundFailedStatus;
            $order->getPayment()->setAdditionalInformation('skrill_status', $responseStatus['status']);
            $order->getPayment()->setTransactionId($mbTransactionId)
                    ->setIsTransactionClosed(0)->save();      
        }

        $comment = Mage::helper('skrill')->getComment($responseStatus,"history","fraud");
        $order->addStatusHistoryComment($comment, false);
        $order->save();
    }

    protected function processPayment($order, $responseStatus)
    {
        if ($responseStatus['status'] == $this->processedStatus) {
            $order->sendNewOrderEmail();
            Mage::helper('skrill')->invoice($order);
            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, 'payment_accepted');
            $order->save();
            
            Mage::getModel('sales/quote')->load($order->getQuoteId())->setIsActive(false)->save();
        } else {
            if ($response['failed_reason_code']) {
                $order->getPayment()->setAdditionalInformation('failed_reason_code', $responseStatus['failed_reason_code']);
            }

            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, false);
            $order->cancel();
            $order->save();
        }
    }

    protected function _redirectError($returnMessage)
    {
        $url = Mage::helper('checkout/url')->getCheckoutUrl();
        Mage::getSingleton('core/session')->addError($returnMessage);
        $this->getResponse()->setRedirect($url);
    }
}
