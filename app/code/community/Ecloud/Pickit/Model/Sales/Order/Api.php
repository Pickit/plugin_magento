<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php 

class Ecloud_Pickit_Model_Sales_Order_Api extends Mage_Sales_Model_Order_Api
{
    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        if ($order->getGiftMessageId() > 0) {
            $order->setGiftMessage(
                Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage()
            );
        }

        $result = $this->_getAttributes($order, 'order');

        $result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
        $result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
        $result['items'] = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
                );
            }
            //Mage::log('Id de orden: ');
            $result['items'][] = $this->_getAttributes($item, 'order_item');
        }

        $result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');

        $result['status_history'] = array();

        foreach ($order->getAllStatusHistory() as $history) {
            $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
        }
        
        //Busco la orden por $orderIncrementId en la tabla pickit_order.    
        $pickitModel = Mage::getModel('pickit/order');
        $pickitOrder = $pickitModel->load($orderIncrementId, 'order_increment_id');

        //Agrego los fields a obtener desde la api.
        if ($pickitOrder){
            $result['pickit']['idCotizacion']    = $pickitOrder['id_cotizacion'];
            $result['pickit']['trackingNumber']  = $pickitOrder['cod_tracking'];
        }

        return $result;
    }
}
?>