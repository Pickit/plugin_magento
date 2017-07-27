<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2014 ecloud solutions ®
 */
?>
<?php 
class Ecloud_Pickit_Adminhtml_Pickit_OrdersController extends Mage_Adminhtml_Controller_Action
{

	public function massImponerAction(){
		$orderIds = $this->getRequest()->getParam('order_ids');

		// Seteamos que estamos haciendo imposicion masiva para no usar el observer
		Mage::register('imposicion_masiva', 1);

		$ordersOk = array();

		foreach ($orderIds as $orderId) {
			$order = Mage::getModel('sales/order')->load($orderId);
			$metodo = $order->getShippingMethod();
			if(preg_match('/pickit/',$metodo)) {
				if(!$order->getShipmentsCollection()->count()){
					$ordersOk[] = $orderId;
				}else{
					Mage::getSingleton('adminhtml/session')->addError('La orden n° '.$order->getIncrementId().' ya ha sido enviada previamente.');
				}
			}else{
				Mage::getSingleton('adminhtml/session')->addError('La orden n° '.$order->getIncrementId().' no corresponde a Pickit');
			}
		}


		$data = array('orders' => $ordersOk);

		if(count($ordersOk) > 0){
			try{
				Mage::dispatchEvent('pickit_generar_retiro_masivo', $data);
			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}			
		}

		Mage::unregister('imposicion_masiva');

		$this->_redirect('adminhtml/sales_order/index');

	}

	public function envioImpresoAction(){
		//Mage::log('envioImpresoAction');
		//Obtengo la orden que llama a la funcion.
		$orderId 	   = $this->getRequest()->getParam('row_id');
		$Order   	   = Mage::getModel('pickit/order')->load($orderId);
		$constanciaURL = $Order->getData('constancia');
		$this->_redirectUrl($constanciaURL);
		
		//Si debo seteaer 'disponible para retiro al imprimir' lo hago.
		$disponibleretiro = Mage::getStoreConfig('carriers/pickitconfig/disponibleretiro',Mage::app()->getStore());		
		if($disponibleretiro == 'imprimir' && $Order['estado'] != 'Disponible para retiro'){
			$response = Mage::getModel('pickit/apicall')->disponibleRetiro($Order['id_transaccion']);
			$Order->setData('estado','Disponible para retiro')->save();
		}
		return;
	}
}

?>