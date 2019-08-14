<?php

class Skrill_UpdateorderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
    	$orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        
        $parameters['trn_id'] = $order->getPayment()->getAdditionalInformation('skrill_transaction_id');

        Mage::log('update order status request', null, 'skrill_log_file.log');
        Mage::log($parameters, null, 'skrill_log_file.log');

        $response = Mage::helper('skrill')->getStatusTrn($parameters);

        Mage::log('update order status response', null, 'skrill_log_file.log');
        Mage::log($response, null, 'skrill_log_file.log');

        if ($response !== false) {
            $invoiceIds = $order->getInvoiceCollection()->getAllIds();
            if (empty($invoiceIds) && $response['status'] == '2') {
                 Mage::helper('skrill')->invoice($order);
            }

            $order->getPayment()->setAdditionalInformation('skrill_status', $response['status']);
            $order->getPayment()->setAdditionalInformation('skrill_payment_type', $response['payment_type']);
            if (isset($response['payment_instrument_country'])) {
                $order->getPayment()->setAdditionalInformation('skrill_issuer_country', $response['payment_instrument_country']);
            }

            $comment = Mage::helper('skrill')->getComment($response);
            $order->addStatusHistoryComment($comment, false);
            $order->save();

            if($order->status == 'invalid_credential' && !$this->isInvalidCredential($order,$response)){
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_accepted')->save();
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('skrill')->__('SUCCESS_GENERAL_UPDATE_PAYMENT'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('skrill')->__('ERROR_UPDATE_BACKEND'));
        }

		$this->_redirect("adminhtml/sales_order/view",array('order_id' => $orderId));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin');
    }

    protected function isInvalidCredential($order, $responseStatus)
    {
        return !($responseStatus['md5sig'] == $this->generateMd5sig($order, $responseStatus));
    }

    protected function generateMd5sig($order, $response)
    {
        $string = Mage::getStoreConfig('payment/skrill_settings/merchant_id', $order->getStoreId()).$response['transaction_id'].strtoupper(Mage::getStoreConfig('payment/skrill_settings/secret_word', $order->getStoreId())).$response['mb_amount'].$response['mb_currency'].$response['status'];

        return strtoupper(md5($string));
    }

}
