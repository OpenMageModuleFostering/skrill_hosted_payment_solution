<?php

class Skrill_UpdateorderController extends Mage_Adminhtml_Controller_Action
{
        public function indexAction()
	{
        	$orderId = $this->getRequest()->getParam('order_id');
	        $order = Mage::getModel('sales/order')->load($orderId);

                $params['mb_trn_id'] = $order->getPayment()->getAdditionalInformation('skrill_mb_transaction_id');

                // check status_trn 3 times if no response.
                for ($i=0; $i < 3; $i++) { 
                        $no_response = false;
                        try {
                                $result = Mage::helper('skrill')->doQuery('status_trn', $params);
                        } catch (Exception $e) {
                                $no_response = true;
                        }
                        if (!$no_response)
                                break;
                }

                if (!$no_response)
                {
                        $response = Mage::helper('skrill')->getResponseArray($result);

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