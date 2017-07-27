<?php
/**
 * @version   0.1.0
 * @author	ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Model_Observer extends Mage_Core_Model_Session_Abstract {

	/**
	* Llama a la funcion cuando la orden fue creada luego del
	* Checkout y almacena los datos en la tabla "pickit_order"
	*/
	public function guardarDataOrden($observer) {
		try {
			$idCotizacion = Mage::getSingleton('core/session')->getIdCotizacion();
			//Unsetear la variable de sesion de la cotizacion
			Mage::getSingleton('core/session')->unsIdCotizacion();
			//  Tomamos todos los datos de la orden
			$metodoenvio = $observer->getEvent()->getOrder()->getShippingMethod();
			if(!$datos = Mage::helper('pickit')->getPickitData($metodoenvio)){
				// Orden no es enviada por PICKIT
				return;
			}
			// fix. setteamos datos de ship porque si la orden viene de admin, vienen vacios
			$ship = $observer->getEvent()->getOrder()->getShippingAddress();
			$bill = $observer->getEvent()->getOrder()->getBillingAddress();
			$datos['nombre']	= $bill->getFirstname();
			$datos['apellido']	= $bill->getLastname();
			$datos['telefono']	= $ship->getTelephone();
			
			if($ship->getEmail() != "") {
				$datos['email'] = $ship->getEmail();
			} elseif($bill->getEmail() != "") {
				$datos['email'] = $bill->getEmail();
			} elseif($observer->getEvent()->getOrder()->getData("customer_email") != "") {
				$datos['email'] = $observer->getEvent()->getOrder()->getData("customer_email");
			} else {
				$datos['email'] = "email@prueba.com";
			}
			

			$campoDni  = 'customer_';
			$campoDni .= Mage::getStoreConfig('carriers/pickitconfig/global_idusuario',Mage::app()->getStore());
			//Mage::log('Campo de DNI: '.$campoDni);

			$dni = $observer->getEvent()->getOrder()->getData($campoDni);
			//Mage::log('DNI: '.$dni);
			//Mage::log('Sucursal: '.$datos['sucursal_retiro']);
			
			
			// 2. Buscamos el ID de la orden y increment id
			$OrderId	= $observer->getEvent()->getOrder()->getId();
			$OrderIncId = $observer->getEvent()->getOrder()->getIncrementId();

			
			//Reemplazamos shipping address por los datos de la sucursal PICKIT.
			$Order				  = $observer->getEvent()->getOrder();
			$orderShippingAddress = $Order->getShippingAddress()->getId();
			$orderShipping 		  = Mage::getModel('sales/order_address')->load($orderShippingAddress);
			
			$sucursal_name 	        = $datos["sucursal_nombre"];
			$sucursal_lastname 	    = $datos["sucursal_cadena"];
			$sucursal_streetcity    = $datos["sucursal_direccion"];
			$sucursal_streetcity    = explode(',', $sucursal_streetcity);
			$sucursal_street   	    = $sucursal_streetcity[0]; // Sucursal domicilio.
			$sucursal_region   	    = $sucursal_streetcity[1]; // Sucursal provincia.
			$sucursal_city   	    = $sucursal_streetcity[2]; // Sucursal ciudad.
		    $sucursal_postcode	    = $datos["sucursal_cp"];
		    $sucursal_phone	    	= $datos["sucursal_tel"];
		    if ( trim($sucursal_phone) == '' ) $sucursal_phone = '-';

		    $sucursal_region_id   	    = Mage::helper('pickit')->getRegionId($sucursal_region, $sucursal_city);
			
			
			$orderShipping
			->setFirstname($sucursal_name)
			->setLastname($sucursal_lastname)
			->setStreet(($sucursal_street))
			->setCity(($sucursal_city))
			->setTelephone($sucursal_phone)
			->setFax('')
			->setPostcode($sucursal_postcode)
			->setRegionId($sucursal_region_id)->save();
			
			
			// 3. Los almacenamos en la tabla "pickit_order"
			$_dataSave = (array(
						'id_orden'				=> intval($OrderId),
						'order_increment_id'	=> $OrderIncId,
						'direccion'				=> $datos['direccion'],
						'localidad'				=> $datos['localidad'],
						'provincia'				=> $datos['provincia'],
						'cp_destino'			=> $datos['cpDestino'],
						'nombre'				=> $datos['nombre'],
						'apellido'				=> $datos['apellido'],
						'telefono'				=> $datos['telefono'],
						'email'					=> $datos['email'],
						'precio'				=> $datos['precio'],
						'valor_declarado'		=> $datos['valorDeclarado'],
						'volumen'				=> $datos['volumen'],
						'peso'					=> $datos['peso'],
						'estado'				=> 'Pendiente',
						'id_cotizacion'			=> $idCotizacion,
						'dni'					=> $dni,
						'datos_sucursal'		=> $datos['sucursal_retiro']
					));

			//Mage::log($_dataSave);

			$newOrder = Mage::getModel('pickit/order')->addData($_dataSave);
			$newOrder->save();

			$this->borrarVariablesEnvio($observer);

		} catch (Exception $e) {
			//Mage::log("Error: " . $e);
		}
	}

	public function imposicionPickit($observer) {
		return $this->generarRetiro($observer);
	}

	
	/**
	* Llama a la funcion cuando desde el Admin Panel
	* se ejecuta el "Ship" y luego "Submit Shipment"
	*/
	public function generarRetiro($observer) {
		Mage::getSingleton('core/session')->unsErrorPickit();
		//Mage::log("Generar retiro (sales order shipping before");
		
		//Mage::log('Entrada a Observer generar retiro');
		// Si entramos al observer mediante imposicion masiva, no hacemos nada del observer
		// ya que generamos los retiros en una evento dedicado
		if (Mage::registry('imposicion_masiva') == 1){
			return;
		}
		//Mage::log('No hay imposicion masiva');
		// 1. Tomamos los datos de la orden segun order id en la tabla "pickit_order"
		$orderId		= $observer->getEvent()->getShipment()->getOrder()->getId();
		$storeid = $observer->getEvent()->getShipment()->getOrder()->getStoreId();
		//$store = Mage::getModel('core/store')->load($observer->getEvent()->getShipment()->getOrder()->getStoreId());

		//Mage::log("Forma de imposicion: ".Mage::getStoreConfig('carriers/pickitconfig/global_imposicion',Mage::app()->getStore()));
		if(Mage::getStoreConfig('carriers/pickitconfig/global_imposicion',$storeid) != "noimponer") {
			//Mage::log('Order id: '.$orderId);
			$order_pickit 	= Mage::getModel('pickit/order')->loadById($orderId);
			$datos 			= $order_pickit->getData();
			//Mage::log('Datos del envio pickit: ');
			//Mage::log(print_r($datos,true));

			if (!$datos) {
				// No es envio con Pickit
				//Mage::log('No es envio pickit');
				return;
			}

			$idCotizacion = $datos["id_cotizacion"];
			$numeroOrden  = $datos["order_increment_id"];
			$emailCliente = $datos["email"];
			//Mage::log("Nro orden: ".$numeroOrden);
			//Mage::log('Id de cotizacion: '.$idCotizacion);
			
			$datos = array($datos);

			//Tomamos los datos del cliente desde el BillingAddress
			$ship = $observer->getEvent()->getShipment()->getOrder()->getBillingAddress();
			//Obtenemos el campo DNI custom desde el seleccionado en la config
			$campoDni  = 'customer_';
			$campoDni .= Mage::getStoreConfig('carriers/pickitconfig/global_idusuario',Mage::app()->getStore());
			//Mage::log('Campo de DNI: '.$campoDni);
			$dni = $observer->getEvent()->getShipment()->getOrder()->getData($campoDni);
			//Mage::log('DNI: '.$dni);

			//Armamos el array de datos del cliente para enviar al WS
			$dataCliente = array('nombre' => $ship->getFirstname(),
				'apellido' => $ship->getLastname(),
				'dni' => $dni,
				'email' => $emailCliente,
				'telefono' => $ship->getTelephone()
				);

			//Mage::log($dataCliente);
			//Llamamos a la api que se conecta con WS para generar la imposicion
			$response = Mage::getModel('pickit/apicall')->iniciar($storeid)->generarEnvio($idCotizacion,$dataCliente,$numeroOrden);
			//die(Mage::log($response));
			if($response != null) {
				if($response["Status"]["Code"] == "503") {
					Mage::log("PICKIT - Error en conexion con WS: ".$response["Status"]["Text"]);
					Mage::getSingleton('core/session')->setErrorPickit("1");
					return;
				}
				if($response["Status"]["Code"] == "999") {
					Mage::log("PICKIT - Error en conexion con WS: ".$response["Status"]["Text"]);
					Mage::getSingleton('core/session')->setErrorPickit("1");
					return;
				}
				
				$tracking = Mage::getSingleton('core/session')->getTrackingPickit();
				Mage::getSingleton('core/session')->unsTrackingPickit();
				
				$nroPickit = $response["Response"]["TransaccionId"];
				
				$info = Mage::getModel('pickit/apicall')->iniciar($storeid)->obtenerDetalleTransaccion($nroPickit);
				//Mage::log('Etiqueta: '.$info["Response"]["UrlEtiqueta"]);
				//Mage::log('Etiqueta: '.$info["Status"]["Text"]);
				$constancia = $info["Response"]["UrlEtiqueta"];
				$estado 	= $info["Response"]["Estado"];

				if($tracking == "" || $info == null || $constancia == "" || $estado == "" ) {
					Mage::getSingleton('core/session')->setErrorPickit("1");
					return;
				}
				
				$shipment 	= $observer->getEvent()->getShipment();
				$track = Mage::getModel('sales/order_shipment_track')
					->setNumber($tracking)
					->setCarrierCode('pickitconfig')
					->setTitle('Pickit');
				$shipment->addTrack($track);

				//El servicio WS de pickit ya envia automaticamente un email cuando se realiza imposicion
				//$this->enviarEmailShipment($shipment);

				Mage::getModel('pickit/order')->loadById($orderId)->setData('cod_tracking',$tracking)->save();
				Mage::getModel('pickit/order')->loadById($orderId)->setData('id_transaccion',$nroPickit)->save();
				Mage::getModel('pickit/order')->loadById($orderId)->setData('constancia',$constancia)->save();
				Mage::getModel('pickit/order')->loadById($orderId)->setData('estado',$estado)->save();
				Mage::getSingleton('core/session')->unsErrorPickit();
			} else {
				Mage::getSingleton('core/session')->setErrorPickit("1");
				return;
			}
		} else {
			Mage::getModel('pickit/order')->loadById($orderId)->setData('estado',"Envio generado sin imposicion")->save();
		}
	}

	/**
	*  Esta funcion se ejecuta al imponer masivamente ordenes a Pickit
	*/
	public function generarRetiroMasivo($observer) {
		//Mage::log("Generar Retiro Masivo - Observer");
		$orderIds = $observer->getData('orders');

		$collection = Mage::getModel('pickit/order')->getCollection()
			->addFieldToFilter('id_orden', $orderIds);

		$this->_imponerColeccion($collection);
	}

	/**
	* Ponemos el envio "Eliminado" en la tabla
	* pickit_order al cancelar una orden desde magento
	*/
	public function cancelarOrden($observer) {
		$orderId	= $observer->getEvent()->getItem()->getOrder()->getId();
		Mage::getModel('pickit/order')->loadById($orderId)->setData("estado","Eliminada")->save();
	}

	/**
	* Despues de guardar el shippment, enviamos el mail al comprador con su tracking code
	*/
	protected function enviarEmailShipment($shipment) {
		// enviamos el mail con el tracking code
		if($shipment){
			$shipment->sendEmail(true,'');
		}
	}

	/**
	* Agregar massAction al sales_order
	*/
	public function addMassAction($observer) {
		//Mage::log('addMassAction');
		$block = $observer->getEvent()->getBlock();
		if(($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction)
			&& $block->getRequest()->getControllerName() == 'sales_order')
		{
			$block->addItem('pickit', array(
				'label' => 'Generar retiros Pickit',
				'url' => $block->getUrl('adminhtml/pickit_orders/massImponer'),
				'confirm' => Mage::helper('sales')->__('Desea imponer las ordenes en Pickit?')
			));
		}
	}

	protected function _imponerColeccion($collection) {
		//Mage::log("Cantidad de ordenes: ".$collection->count());
		foreach ($collection as $orden) {
			Mage::getSingleton('core/session')->unsErrorPickit();
			//Mage::log("Entrada a array de ordenes - RetiroMasivo");
			if($orden['cod_tracking'] == "" && $orden['id_transaccion'] == 0 && $orden['id_cotizacion'] != 0) {				
				$orderObj = Mage::getModel('sales/order')->loadByIncrementId($orden["order_increment_id"]);
				//$store = Mage::getModel('core/store')->load($orderObj->getStoreId());
				if(Mage::getStoreConfig('carriers/pickitconfig/global_imposicion',$orderObj->getStoreId()) != "noimponer") {
					$orderIncrement = $orden["order_increment_id"];
					//Mage::log('Id Orden Pickit'.$orden["id"]);
					//Mage::log('Id Increment Pickit: '.$orden["increment_id"]);
					//Mage::log('Id Increment Pickit: '.$orderIncrement);
					$idCotizacion = $orden["id_cotizacion"];
					//Mage::log('Id Cotizacion: '.$idCotizacion);
					//Armamos el array de datos del cliente para enviar al WS
					$dataCliente = array(
						'nombre' => $orden["nombre"],
						'apellido' => $orden["apellido"],
						'dni' => $orden["dni"],
						'email' => $orden["email"],
						'telefono' => $orden["telefono"]
						);
					//Mage::log($dataCliente);
					//Llamamos a la api que se conecta con WS para generar la imposicion
					$response = Mage::getModel('pickit/apicall')->iniciar($orderObj->getStoreId())->generarEnvio($idCotizacion,$dataCliente,$orden["order_increment_id"]);
					if($response != null) {
						if($response["Status"]["Code"] == "503") {
							Mage::log("PICKIT - Error en conexion con WS: ".$response["Status"]["Text"]);
							Mage::getSingleton('core/session')->setErrorPickit("1");
						}
						$tracking = Mage::getSingleton('core/session')->getTrackingPickit();
						Mage::getSingleton('core/session')->unsTrackingPickit();
						
						$nroPickit = $response["Response"]["TransaccionId"];
						
						Mage::getSingleton('core/session')->setStoreIdPickit($store);
						$info = Mage::getModel('pickit/apicall')->iniciar($orderObj->getStoreId())->obtenerDetalleTransaccion($nroPickit);
						Mage::getSingleton('core/session')->unsStoreIdPickit();
						//Mage::log('Etiqueta: '.$info["Response"]["UrlEtiqueta"]);
						$constancia = $info["Response"]["UrlEtiqueta"];
						$estado 	= $info["Response"]["Estado"];

						Mage::getModel('pickit/order')->loadByOrderIncrementId($orden["order_increment_id"])->setData('cod_tracking',$tracking)->save();
						Mage::getModel('pickit/order')->loadByOrderIncrementId($orden["order_increment_id"])->setData('id_transaccion',$nroPickit)->save();
						Mage::getModel('pickit/order')->loadByOrderIncrementId($orden["order_increment_id"])->setData('constancia',$constancia)->save();
						Mage::getModel('pickit/order')->loadByOrderIncrementId($orden["order_increment_id"])->setData('estado',$estado)->save();
						
						//Armar objetos de shipment y track
						//order obj lo armo afuera para poder mandar el store
						$itemQty		= $orderObj->getItemsCollection()->count();
						$shipment 		= Mage::getModel('sales/service_order', $orderObj)->prepareShipment($itemQty);
						$shipment 		= new Mage_Sales_Model_Order_Shipment_Api();
						$shipmentId 	= $shipment->create($orderObj->getIncrementId(), array(), 'Enviado por Pickit', true, true);
						$shipment 		= Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);

						$track = Mage::getModel('sales/order_shipment_track')
							->setNumber($tracking)
							->setCarrierCode('pickitconfig')
							->setTitle('Pickit');
						$shipment->addTrack($track)->save();
					} else {
						Mage::getSingleton('core/session')->setErrorPickit("1");
					}
				} else {
					//Actualizar estado de orden de Pickit a Envio generado sin imposicion
					Mage::getModel('pickit/order')->loadByOrderIncrementId($orden["order_increment_id"])->setData('estado',"Envio generado sin imposicion")->save();
				}
			}
			if(Mage::getSingleton('core/session')->getErrorPickit())
				Mage::getSingleton('adminhtml/session')->addError('PickIt | La orden n° '.$orden["order_increment_id"].' no pudo ser enviada. Revise las credenciales del WS.');
		}
	}

	public function borrarVariablesEnvio(Varien_Event_Observer $observer) {
		if(Mage::getSingleton('core/session')->getUrlPickit())
			Mage::getSingleton('core/session')->unsUrlPickit();

		if(Mage::getSingleton('core/session')->getIdCotizacion())
			Mage::getSingleton('core/session')->unsIdCotizacion();

		if(Mage::getSingleton('core/session')->getPuntoSeleccionado())
			Mage::getSingleton('core/session')->unsPuntoSeleccionado();

		if(Mage::getSingleton('core/session')->getIdPuntoPickit())
			Mage::getSingleton('core/session')->unsIdPuntoPickit();

		if(Mage::getSingleton('core/session')->getShippingOriginal())
			Mage::getSingleton('core/session')->unsShippingOriginal();
	}

	/*Funcion a ejecutar por Cron. Impone todas las ordenes Pickit.*/
	public function cronImponer() {
		$cronEnable = Mage::getStoreConfig('carriers/pickitconfig/global_imposicion',Mage::app()->getStore());
		if ($cronEnable != 'automatica'){
			//Mage::log('Imposicion manual.');
			return;
		}
		//Mage::log('Imponemos desde el cron.');
		//Primero debemos obtener todas las ordenes de Pickit con un determinado estado'
		//En la primera version dejaremos que este estado sea el de Pendiente
		$orders = Mage::getModel('pickit/order')->getCollection()->addFieldToFilter('estado','Pendiente');
		$this->_imponerColeccion($orders);
	}

	public function cambiarDireccionEnvio($observer) {
		/*
		//Mage::log("Shipping Method guardado");
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		//$quote = $observer->getQuote();
		$shippingMethod = $quote->getShippingAddress()->getShippingMethod();
		//Mage::log("Shipping Method: ".$shippingMethod);
		if($shippingMethod == 'pickitconfig_pickitconfig') {
			$shippingAd = $quote->getShippingAddress()->getShippingDescription();
			$datos = Mage::helper('pickit')->getPickitData($shippingMethod);
			//$datos["sucursal_nombre"]    = $response["Response"]["PuntoPickit"]["Nombre"];
            //$datos["sucursal_cadena"]    = $response["Response"]["PuntoPickit"]["Cadena"];
            //$datos["sucursal_direccion"] = $response["Response"]["PuntoPickit"]["Direccion"];
            //$datos["sucursal_cp"]        = $response["Response"]["PuntoPickit"]["CodigoPostal"];
            //$datos["sucursal_tel"]       = $response["Response"]["PuntoPickit"]["Telefono"];
            $shippingAddress = $quote->getShippingAddress();
			$shippingAddress
       			->setFirstname($datos["sucursal_nombre"])
				->setLastname($datos["sucursal_cadena"])
				->setStreet($datos["sucursal_direccion"])
				->setCity(($dir[1]))
				->setTelephone($datos["sucursal_tel"])
				->setPostcode($datos["sucursal_cp"])
				->setRegion('')
				->save();
		}
		*/

		//Mage::log("Shipping Method guardado");
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        //$quote = $observer->getQuote();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        //Mage::log("Shipping Method: ".$shippingMethod);
        if($shippingMethod == 'pickitconfig_pickitconfig') {
            // $shippingAd = $quote->getShippingAddress()->getShippingDescription();
            if(!$datos = Mage::helper('pickit')->getPickitData($shippingMethod)){
                // Orden no es enviada por PICKIT
                return;
            }

            if(!(Mage::getSingleton('core/session')->getShippingOriginal())) {
				//Direccion no fue seteada aun
				$shipOriginal = $quote->getShippingAddress()->getData();
				Mage::log($shipOriginal);
				Mage::getSingleton('core/session')->setShippingOriginal($shipOriginal);
			}

            $sucursal_name             = $datos["sucursal_nombre"];
            $sucursal_lastname         = $datos["sucursal_cadena"];
            $sucursal_streetcity    = $datos["sucursal_direccion"];
            $sucursal_streetcity    = explode(',', $sucursal_streetcity);
            
            $sucursal_street           = $sucursal_streetcity[0]; // Sucursal domicilio.
            $sucursal_region           = $sucursal_streetcity[1]; // Sucursal provincia.
            $sucursal_city           = $sucursal_streetcity[2]; // Sucursal ciudad.
            $sucursal_postcode        = $datos["sucursal_cp"];
            $sucursal_phone            = $datos["sucursal_tel"];
            
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress
	            ->setFirstname($sucursal_name)
	            ->setLastname($sucursal_lastname)
	            ->setStreet(($sucursal_street))
	            ->setCity(($sucursal_city))
	            ->setTelephone($sucursal_phone)
	            ->setFax('')
	            ->setPostcode($sucursal_postcode)
	            ->setRegion($sucursal_region)->save();
            
        } else {
        	if(Mage::getSingleton('core/session')->getShippingOriginal()) {
				//Direccion ya seteada
				$quote->getShippingAddress()->setData(Mage::getSingleton('core/session')->getShippingOriginal());
				$quote->getShippingAddress()->setShippingMethod($shippingMethod);
				Mage::getSingleton('core/session')->unsShippingOriginal();
			}
        }	
	}
}
?>