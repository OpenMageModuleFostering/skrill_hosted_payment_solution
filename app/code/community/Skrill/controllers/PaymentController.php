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
        $session = Mage::getSingleton('checkout/session');

        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        
        if (!$order->getId())
            Mage::throwException('No order for processing found');
        
        $this->_getPostResponseActionUrl($order);
    }

    private function generateMd5sig($order, $response)
    {
        $string = Mage::getStoreConfig('payment/skrill_settings/merchant_id', $order->getStoreId()).$response['transaction_id'].strtoupper(Mage::getStoreConfig('payment/skrill_settings/secret_word', $order->getStoreId())).$response['mb_amount'].$response['mb_currency'].$response['status'];

        return strtoupper(md5($string));
    }

    protected function _checkStatusPayment($trn_id, &$result)
    {
        $params['trn_id'] = $trn_id;
        // check status_trn 3 times if no response.
        for ($i=0; $i < 3; $i++) { 
            $no_response = false;
            try {
                $result = Mage::helper('skrill')->doQuery('status_trn', $params);
            } catch (Exception $e) {
                $no_response = true;
            }
            if (!$no_response && $result)
            {
                return false;
                break;
            }
        }
        return true;
    }

    protected function _checkFraud($order, $response)
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $quoteData = $quote->getData();
        $grandTotal = (float) $quoteData['grand_total'];
        $amount = (float) $response['amount'];
        if ($response['amount'])
            return !( ($grandTotal == $amount) && ($response['md5sig'] == $this->generateMd5sig($order, $response)) );
        else
            return false;
    }

    protected function _redirectError($returnMessage, $url='checkout/onepage')
    {
        Mage::getSingleton('core/session')->addError($returnMessage);
        $this->_redirect($url, array('_secure'=>true));
    }

    private function _getPostResponseActionUrl(Mage_Sales_Model_Order $order)
    {
        if ( isset($_GET['transaction_id']) )
        {
            $no_response = $this->_checkStatusPayment($_GET['transaction_id'], $result);

            if ($no_response)
            {
                $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_NORESPONSE'));
            }
            else
            {
                $response = Mage::helper('skrill')->getResponseArray($result);
                $is_fraud = false;

                $order->getPayment()->setAdditionalInformation('skrill_transaction_id', $response['transaction_id']);
                $order->getPayment()->setAdditionalInformation('skrill_mb_transaction_id', $response['mb_transaction_id']);
                $order->getPayment()->setAdditionalInformation('skrill_ip_country', $response['ip_country']);
                $order->getPayment()->setAdditionalInformation('skrill_status', $response['status']);
                $order->getPayment()->setAdditionalInformation('skrill_payment_type', $response['payment_type']);
                $order->getPayment()->setAdditionalInformation('skrill_issuer_country', $response['payment_instrument_country']);
                $order->getPayment()->setAdditionalInformation('skrill_currency', $response['currency']);

                if ($is_fraud)
                {
                    $comment = Mage::helper('skrill')->getComment($response);
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();

                    $params['mb_transaction_id'] = $response['mb_transaction_id'];
                    $params['amount'] = $response['mb_amount'];

                    $xml_result = Mage::helper('skrill')->doRefund('prepare', $params);

                    $sid = (string) $xml_result->sid;

                    $xml_result = Mage::helper('skrill')->doRefund('refund', $sid);

                    $status = (string) $xml_result->status;
                    $mb_trans_id = (string) $xml_result->mb_transaction_id;

                    if ($status == '2') {    
                        $response['status'] = "-4";
                        $order->getPayment()->setAdditionalInformation('skrill_status', $response['status']);
                        $order->getPayment()->setTransactionId($mb_trans_id)
                                ->setIsTransactionClosed(1)->save();
                    } else {
                        $response['status'] = "-5";
                        $order->getPayment()->setAdditionalInformation('skrill_status', $response['status']);
                        $order->getPayment()->setTransactionId($mb_trans_id)
                                ->setIsTransactionClosed(0)->save();                    
                    }
                    $comment = Mage::helper('skrill')->getComment($response,"history","fraud");
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();

                    $this->_redirectError(Mage::helper('skrill')->__('ERROR_GENERAL_FRAUD_DETECTION'));
                }
                else
                {
                    if ($response['status'] == '2') 
                    {
                        Mage::helper('skrill')->invoice($order);
                        $comment = Mage::helper('skrill')->getComment($response);
                        $order->addStatusHistoryComment($comment, false);
                        $order->save();
                        $order->sendNewOrderEmail();

                        Mage::getModel('sales/quote')->load($order->getQuoteId())->setIsActive(false)->save();
                        $this->_redirect('checkout/onepage/success');
                    } 
                    else if ($response['status'] == '-2') 
                    {
                        $comment = Mage::helper('skrill')->getComment($response);
                        $order->addStatusHistoryComment($comment, false);
                        $order->save();

                        $this->_redirectError(Mage::helper('skrill')->__(Mage::helper('skrill')->getSkrillErrorMapping($response['failed_reason_code'])));
                    }
                    else 
                    {
                        $comment = Mage::helper('skrill')->getComment($response);
                        $order->addStatusHistoryComment($comment, false);
                        $order->save();

                        $this->_redirectError(Mage::helper('skrill')->__('SKRILL_ERROR_99_GENERAL'));
                    }            
                }
            }
        }
        else
        {
            $this->_redirectError(Mage::helper('skrill')->__('SKRILL_ERROR_99_GENERAL'));
        }
    }
}