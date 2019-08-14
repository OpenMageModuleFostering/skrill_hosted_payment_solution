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

        $this->processReturnUrl($order);
    }

    public function cancelResponseAction()
    {
        Mage::log('cancel action', null, 'skrill_log_file.log');

        $session = Mage::getSingleton('checkout/session');
        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());

        if (!$order->getId())
            Mage::throwException('No order for processing found');

        $order->cancel();
        $order->save();
        $this->_redirectError('ERROR_GENERAL_CANCEL');
    }

    protected function inActiveQuote($order)
    {
        Mage::getModel('sales/quote')->load($order->getQuoteId())->setIsActive(false)->save();
    }

    protected function processReturnUrl($order)
    {
        Mage::log('process return url', null, 'skrill_log_file.log');

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        Mage::log('payment additional information', null, 'skrill_log_file.log');
        Mage::log($additionalInformation, null, 'skrill_log_file.log');

        if (isset($additionalInformation['skrill_status_url_response'])) {
            if ($additionalInformation['skrill_status'] == Skrill_Model_Method_Skrill::PENDING_STATUS
                || $additionalInformation['skrill_status'] == Skrill_Model_Method_Skrill::PROCESSED_STATUS
            ) {
                $this->_redirect('checkout/onepage/success');
            } elseif ($additionalInformation['skrill_status'] == Skrill_Model_Method_Skrill::FAILED_STATUS) {
                $failedReasonCode = $additionalInformation['failed_reason_code'];
                $this->_redirectError(Mage::helper('skrill')->getSkrillErrorMapping($failedReasonCode));
            } elseif ($additionalInformation['skrill_status'] == Skrill_Model_Method_Skrill::REFUNDED_STATUS
                || $additionalInformation['skrill_status'] == Skrill_Model_Method_Skrill::REFUNDFAILED_STATUS) {
                $this->_redirectError('ERROR_GENERAL_FRAUD_DETECTION');
            } else {
                $this->_redirectError('SKRILL_ERROR_99_GENERAL');
            }
        } else {
            $this->inActiveQuote($order);
            $message = Mage::helper('skrill')->__('FRONTEND_MESSAGE_YOUR_ORDER').' '.
                Mage::getStoreConfig('general/store_information/name').' '.
                Mage::helper('skrill')->__('FRONTEND_MESSAGE_INPROCESS').' '.
                Mage::helper('skrill')->__('FRONTEND_MESSAGE_PLEASE_BACK_AGAIN');
            $this->_redirectWarning($message);
        }
    }

    public function handleStatusResponseAction()
    {
        $status = $this->getRequest()->getParam('status');

        Mage::log('process status url with status : '.$status, null, 'skrill_log_file.log');

        if (isset($status)) {
            $orderId = $this->getRequest()->getParam('orderId');
            $responseStatus = $this->getResponseStatus();

            Mage::log('status url response', null, 'skrill_log_file.log');
            Mage::log($responseStatus, null, 'skrill_log_file.log');

            $order = Mage::getSingleton('sales/order');
            $order->loadByIncrementId($orderId);

            $order->getPayment()->setAdditionalInformation(
                'skrill_status_url_response',
                serialize($responseStatus)
            )->save();

            if ($order->getPayment()->getAdditionalInformation('skrill_status') == Skrill_Model_Method_Skrill::PENDING_STATUS) {
                $this->updateOrderStatus($order, $responseStatus);
            } else {
                $this->validatePayment($order, $responseStatus);
            }
        }
    }

    protected function validatePayment($order, $responseStatus)
    {
        Mage::log('validate payment', null, 'skrill_log_file.log');

        if ($responseStatus['payment_type'] == 'NGP') {
            $responseStatus['payment_type'] = 'OBT';
        }

        $versionData = Mage::helper('skrill')->getMerchantData($order->getStoreId());
        Mage::helper('skrill/versionTracker')->sendVersionTracker($versionData);

        $this->saveAdditionalInformation($order, $responseStatus);

        $isFraud = $this->isFraud($order, $responseStatus);
        $isFraudLog = ($isFraud) ? 'true' : 'false';
        Mage::log('is Fraud : '.$isFraudLog, null, 'skrill_log_file.log');

        if ($isFraud) {
            $this->processFraud($order, $responseStatus);
        } else {
            $this->processPayment($order, $responseStatus);
        }

    }

    protected function getResponseStatus()
    {
        $responseStatus = array();
        foreach ($this->getRequest()->getParams() as $responseName => $responseValue) {
            $responseStatus[strtolower($responseName)] = $responseValue;
        }
        return $responseStatus;
    }

    protected function saveAdditionalInformation($order, $responseStatus)
    {
        $payment = $order->getPayment();
        if (isset($responseStatus['transaction_id'])) {
            $payment->setAdditionalInformation('skrill_transaction_id', $responseStatus['transaction_id']);
        }
        if (isset($responseStatus['mb_transaction_id'])) {
            $payment->setAdditionalInformation('skrill_mb_transaction_id', $responseStatus['mb_transaction_id']);
        }
        if (isset($responseStatus['ip_country'])) {
            $payment->setAdditionalInformation('skrill_ip_country', $responseStatus['ip_country']);
        }
        if (isset($responseStatus['status'])) {
            $payment->setAdditionalInformation('skrill_status', $responseStatus['status']);
        }
        if (isset($responseStatus['payment_type'])) {
            $payment->setAdditionalInformation('skrill_payment_type', $responseStatus['payment_type']);
        }
        if (isset($responseStatus['payment_instrument_country'])) {
            $payment->setAdditionalInformation('skrill_issuer_country', $responseStatus['payment_instrument_country']);
        }
        if (isset($responseStatus['currency'])) {
            $payment->setAdditionalInformation('skrill_currency', $responseStatus['currency']);
        }
        $payment->save();
    }

    protected function setNumberFormat($number)
    {
        $number = (float) str_replace(',','.',$number);
        return number_format($number, 2, '.', '');
    }

    protected function isFraud($order, $responseStatus)
    {
        return !($responseStatus['md5sig'] == $this->generateMd5sig($order, $responseStatus));
    }

    protected function generateMd5sig($order, $response)
    {
        $string = Mage::getStoreConfig('payment/skrill_settings/merchant_id', $order->getStoreId()).$response['transaction_id'].strtoupper(Mage::getStoreConfig('payment/skrill_settings/secret_word', $order->getStoreId())).$response['mb_amount'].$response['mb_currency'].$response['status'];

        return strtoupper(md5($string));
    }

    protected function processFraud($order, $responseStatus)
    {
        Mage::log('process Fraud', null, 'skrill_log_file.log');

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

        if ($status == Skrill_Model_Method_Skrill::PROCESSED_STATUS) {
            $responseStatus['status'] = Skrill_Model_Method_Skrill::REFUNDED_STATUS;
            $order->getPayment()->setAdditionalInformation('skrill_status', $responseStatus['status']);
            $order->getPayment()->setTransactionId($mbTransactionId)
                    ->setIsTransactionClosed(1)->save();
        } else {
            $responseStatus['status'] = Skrill_Model_Method_Skrill::REFUNDFAILED_STATUS;
            $order->getPayment()->setAdditionalInformation('skrill_status', $responseStatus['status']);
            $order->getPayment()->setTransactionId($mbTransactionId)
                    ->setIsTransactionClosed(0)->save();
        }

        $comment = Mage::helper('skrill')->getComment($responseStatus,"history","fraud");
        $order->cancel();
        $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, 'fraud')->save();
        $order->addStatusHistoryComment($comment, false);
        $order->save();
    }

    protected function processPayment($order, $responseStatus)
    {
        Mage::log('process payment', null, 'skrill_log_file.log');

        if ($responseStatus['status'] == Skrill_Model_Method_Skrill::PENDING_STATUS) {
            $order->sendNewOrderEmail();
            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, true)->save();
            $order->addStatusHistoryComment($comment, false)->save();
            $this->inActiveQuote($order);
        } elseif ($responseStatus['status'] == Skrill_Model_Method_Skrill::PROCESSED_STATUS) {
            $order->sendNewOrderEmail();
            Mage::helper('skrill')->invoice($order);
            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, 'payment_accepted')->save();
            $this->inActiveQuote($order);
        } else {
            if ($responseStatus['failed_reason_code']) {
                $order->getPayment()->setAdditionalInformation(
                    'failed_reason_code',
                    $responseStatus['failed_reason_code']
                );
            }
            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, false);
            $order->cancel();
            $order->save();
        }
    }

    protected function updateOrderStatus($order, $responseStatus)
    {
        Mage::log('update order status with status : '.$responseStatus['status'], null, 'skrill_log_file.log');

        if ($responseStatus['status'] == Skrill_Model_Method_Skrill::PROCESSED_STATUS) {
            Mage::helper('skrill')->invoice($order);
            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, 'payment_accepted')->save();
        } elseif ($responseStatus['status'] == Skrill_Model_Method_Skrill::FAILED_STATUS) {
            if ($responseStatus['failed_reason_code']) {
                $order->getPayment()->setAdditionalInformation(
                    'failed_reason_code',
                    $responseStatus['failed_reason_code']
                );
            }
            $comment = Mage::helper('skrill')->getComment($responseStatus);
            $order->addStatusHistoryComment($comment, false);
            $order->cancel();
            $order->save();
        }
    }

    public function handleRefundStatusResponseAction()
    {
        $status = $this->getRequest()->getParam('status');

        Mage::log('process refund status url with status : '.$status, null, 'skrill_log_file.log');

        if (isset($status)) {
            $orderId = $this->getRequest()->getParam('orderId');
            $responseStatus = $this->getResponseStatus();

            Mage::log('refund status url response', null, 'skrill_log_file.log');
            Mage::log($responseStatus, null, 'skrill_log_file.log');

            $order = Mage::getSingleton('sales/order');
            $order->loadByIncrementId($orderId);

            $order->getPayment()->setAdditionalInformation(
                'skrill_refund_status_url_response',
                serialize($responseStatus)
            )->save();

            if ($order->getPayment()->getAdditionalInformation('skrill_refund_status') == Skrill_Model_Method_Skrill::REFUNDPENDING_STATUS) {
                if ($responseStatus['status'] == Skrill_Model_Method_Skrill::PROCESSED_STATUS) {
                    $responseStatus['status'] = Skrill_Model_Method_Skrill::REFUNDED_STATUS;
                    $comment = Mage::helper('skrill')->getComment($responseStatus, false, 'refundStatus');
                    $order->addStatusHistoryComment($comment, false)->save();
                } else {
                    $responseStatus['status'] = Skrill_Model_Method_Skrill::REFUNDFAILED_STATUS;
                }
                $order->getPayment()->setAdditionalInformation('skrill_refund_status', $responseStatus['status'])->save();
            }
        }
    }

    protected function _redirectWarning($message)
    {
        Mage::log('redirect warning', null, 'skrill_log_file.log');

        $url = Mage::getUrl('', array('_secure' => true));
        Mage::getSingleton('core/session')->addWarning($message);
        $this->getResponse()->setRedirect($url);
    }

    protected function _redirectError($returnMessage)
    {
        Mage::log('redirect error', null, 'skrill_log_file.log');

        $url = Mage::helper('checkout/url')->getCheckoutUrl();
        Mage::getSingleton('core/session')->addError(Mage::helper('skrill')->__($returnMessage));
        $this->getResponse()->setRedirect($url);
    }
}
