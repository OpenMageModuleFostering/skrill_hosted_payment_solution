<?php

class Skrill_UpdateorderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
	{
    	$orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $parameters['mb_trn_id'] = $order->getPayment()->getAdditionalInformation('skrill_mb_transaction_id');
        $response = Mage::helper('skrill')->getStatusTrn($parameters);

        if ($response !== false) {
            $invoiceIds = $order->getInvoiceCollection()->getAllIds();
            if (empty($invoiceIds) && $response['status'] == '2') {
                 Mage::helper('skrill')->invoice($order);
            }

            $order->getPayment()->setAdditionalInformation('skrill_status', $response['status']);
            $order->getPayment()->setAdditionalInformation('skrill_payment_type', $response['payment_type']);
            $order->getPayment()->setAdditionalInformation('skrill_issuer_country', $response['payment_instrument_country']);

            $comment = Mage::helper('skrill')->getComment($response);
            $order->addStatusHistoryComment($comment, false);
            $order->save();
        }

		$this->_redirect("adminhtml/sales_order/view",array('order_id' => $orderId));
	}
}