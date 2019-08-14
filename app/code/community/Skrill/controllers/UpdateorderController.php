<?php

class Skrill_UpdateorderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
    	$orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $method = $order->getPayment()->getMethodInstance();
        $skrillSettings = $method->getSkrillSettings();

        $parameters['email'] = $skrillSettings['merchant_account'];
        $parameters['password'] = $skrillSettings['api_passwd'];
        $parameters['trn_id'] = $order->getPayment()->getAdditionalInformation('skrill_transaction_id');

        $parametersLog = $parameters;
        $parametersLog['password'] = '*****';

        Mage::log('update order status request', null, 'skrill_log_file.log');
        Mage::log($parametersLog, null, 'skrill_log_file.log');

        $response = Mage::helper('skrill')->getStatusTrn($parameters);

        Mage::log('update order status response', null, 'skrill_log_file.log');
        Mage::log($response, null, 'skrill_log_file.log');

        if ($response) {
            $generatedSignatured = $method->generateMd5sigByResponse($response);
            $isCredentialValid = $method->isPaymentSignatureEqualsGeneratedSignature($response['md5sig'], $generatedSignatured);

            if ($isCredentialValid) {
                $invoiceIds = $order->getInvoiceCollection()->getAllIds();
                if (empty($invoiceIds) && $response['status'] == '2') {
                     Mage::helper('skrill')->invoice($order);
                     $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_accepted')->save();
                }

                if ($order->getStatus() == 'invalid_credential' && $response['status'] == '2'){
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_accepted')->save();
                }

                $order->getPayment()->setAdditionalInformation('skrill_status', $response['status']);
                $order->getPayment()->setAdditionalInformation('skrill_payment_type', $response['payment_type']);
                if (isset($response['payment_instrument_country'])) {
                    $order->getPayment()->setAdditionalInformation('skrill_issuer_country', $response['payment_instrument_country']);
                }
                $order->getPayment()->save();

                $comment = Mage::helper('skrill')->getComment($response);
                $order->addStatusHistoryComment($comment, false);
                $order->save();

                Mage::log('process update order from backend : success', null, 'skrill_log_file.log');
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('skrill')->__('SUCCESS_GENERAL_UPDATE_PAYMENT'));
            } else {
                Mage::log('process update order from backend : invalid credential', null, 'skrill_log_file.log');
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('skrill')->__('ERROR_UPDATE_BACKEND'));
            }
        } else {
            Mage::log('process update order from backend : failed', null, 'skrill_log_file.log');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('skrill')->__('ERROR_UPDATE_BACKEND'));
        }

		$this->_redirect("adminhtml/sales_order/view",array('order_id' => $orderId));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin');
    }
}
