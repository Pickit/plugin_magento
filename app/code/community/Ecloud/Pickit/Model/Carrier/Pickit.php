<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Model_Carrier_Pickit extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {  

    protected $_code = 'pickitconfig';
    protected $distancia_final_txt  = '';
    protected $duracion_final       = '';
    protected $mode  = '';
    protected $envio = '';
    protected $api;

    /** 
    * Recoge las tarifas del método de envío basados ​​en la información que recibe de $request
    * 
    * @param Mage_Shipping_Model_Rate_Request $data 
    * @return Mage_Shipping_Model_Rate_Result 
    */ 
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        //Mage::log('Entrada a CollectRates de Pickit - Pickit.php');
        Mage::getSingleton('core/session')->unsEnvioPickit();

        $datos = $this->getShippingDetails($request);

        // Seteamos las reglas
        if(isset($datos["freeBoxes"]))
            $this->setFreeBoxes($datos["freeBoxes"]);
        
        $cart   = Mage::getSingleton('checkout/cart');
        $quote  = $cart->getQuote();
        $shippingAddress        = $quote->getShippingAddress();
        $datos["cpDestino"]     = $request->getDestPostcode();
        $datos["localidad"]     = $request->getDestCity();
        $datos["provincia"]     = $request->getDestRegionCode();
        $datos["direccion"]     = $request->getDestStreet();
        $datos["nombre"]        = $shippingAddress->getData('firstname');
        $datos["apellido"]      = $shippingAddress->getData('lastname');
        $datos["telefono"]      = $shippingAddress->getData('telephone');
        $datos["email"]         = $shippingAddress->getData('email');

        $result = Mage::getModel('shipping/rate_result');

        // Optimizacion con OneStepCheckout
        $error_msg = Mage::helper('pickit')->__("Completá los datos para poder calcular el costo de su pedido.");
        if ($datos["cpDestino"]=="" && $datos["localidad"]=="" && $datos["provincia"]=="" && $datos["direccion"]=="") {
            $error = Mage::getModel('shipping/rate_result_error'); 
            $error->setCarrier($this->_code); 
            $error->setCarrierTitle($this->getConfigData('title')); 
            $error->setErrorMessage($error_msg); 
            return $error;
        }

        if ($this->_code == "pickitconfig" && $this->getPickitConfigData('carriers/pickitconfig/active') == 1) {
            $response = $this->_getPrecioPickit($datos,$request);
            if(is_string($response)){
                //Mage::log('Hubo un error en la respuesta de _getPrecioPickit - Pickit.php');
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($response);
                return $error;
            } else {
                $result->append($response);
            }
        }

        return $result;
    }  

    /** 
    * Arma el precio y la información del servicio de Pickit según el parametro $data
    * 
    * @param Datos del usuario y el carrito de compras $data 
    * @return Los datos para armar el Método de envío $rate 
    */  
    protected function _getPrecioPickit($datos,$request){
        //Mage::log('Entrada a metodo _getPrecioPickit - Pickit.php');
        $rate = Mage::getModel('shipping/rate_result_method');
        $price = 0;
        $sucursal = '';
        /*
        *   Tenemos 2 banderas guardadas en la sesion: banderaCotizacion y banderaPunto
        *   Ambas deben ser verdaderas para poder mostrar el valor de la transaccion
        *   Ademas se va a almacenar el id de cotizacion y el id puntopickit en la sesion
        */
        if(Mage::getSingleton('core/session')->getUrlPickit() && Mage::getSingleton('core/session')->getIdCotizacion()) {
            $idCotizacion = Mage::getSingleton('core/session')->getIdCotizacion();
            //Mage::log('Id de cotizacion: '.$idCotizacion);
            //Ver si el punto ya esta seleccionado.

            if(Mage::getSingleton('core/session')->getPuntoSeleccionado()) {
                //Mage::log('Punto seleccionado?: '.Mage::getSingleton('core/session')->getPuntoSeleccionado());
                //Si esta seleccionado, obtener el id y guardarlo en sesion
                $response = $this->getApi()->iniciar(null)->obtenerInformacionPuntoSeleccionado($idCotizacion);
                //Mage::log($response);
                Mage::getSingleton('core/session')->setIdPuntoPickit($response["Response"]["PuntoPickit"]["PuntoPickitId"]);
                
                //Obtenemos configuracion pickit para setear el precio.
                $config_precio = $this->getPickitConfigData('carriers/pickitconfig/precio');

                if ($config_precio == 'fijo'){
                    //Mage::log('Precio fijo');
                    $price = $this->getPickitConfigData('carriers/pickitconfig/preciofijo');
                }elseif ($config_precio == 'automatico') {
                    //Mage::log('Precio automatico');
                    $price = $response["Response"]["ValorTransaccion"];
                }

                if ($config_precio == 'porcentaje'){
                    //Mage::log('Precio con porcentaje');
                    $price = $price + ($price * Mage::getStoreConfig('carriers/pickitconfig/regla') / 100);
                }

                //Mage::log('Config precio: '.$config_precio);
                //Mage::log('Precio del envio: '.$price);
                //Mage::log('Precio del WS: '.$response["Response"]["ValorTransaccion"]);
                $sucursal .= $response["Response"]["PuntoPickit"]["Nombre"].', '.$response["Response"]["PuntoPickit"]["Cadena"].' / '.$response["Response"]["PuntoPickit"]["Direccion"].' / '.$response["Response"]["PuntoPickit"]["CodigoPostal"].' / '.$response["Response"]["PuntoPickit"]["Telefono"];
                //Mage::log($response["Response"]);
                $datos["precio"] = $price;
                $datos["sucursal_nombre"]    = $response["Response"]["PuntoPickit"]["Nombre"];
                $datos["sucursal_cadena"]    = $response["Response"]["PuntoPickit"]["Cadena"];
                $datos["sucursal_direccion"] = $response["Response"]["PuntoPickit"]["Direccion"];
                $datos["sucursal_cp"]        = $response["Response"]["PuntoPickit"]["CodigoPostal"];
                $datos["sucursal_tel"]       = $response["Response"]["PuntoPickit"]["Telefono"];
                $datos["sucursal_retiro"]    = $sucursal;
            }
        } else {
            /*
            * Como no esta seteado el url de pickit ni el id de cotizacion (vienen juntos)
            * hacemos lo necesario para setearlos
            */
            //Traemos todos los items del carro para armar el array de articulos
            $items = Mage::getSingleton('checkout/cart')->getQuote()->getAllItems();
            $articulos = array();

            //ToDo:
            //1. Tomar configuracion de Unidad de peso a tomar del producto ( Litros, Kilogramos, PV).
            $unidad_peso = $this->getPickitConfigData('carriers/pickitconfig/unidadpeso');
            //2. Obtengo el atributo que usamos para peso de producto.
            $atributo_peso = $this->getPickitConfigData('carriers/pickitconfig/atributo_peso');

            foreach ($items as $pr) {
                //Mage::log('Cantidad: '.$pr->getQty());
                for ($i=1; $i <= $pr->getQty() ; $i++) {
                    if($pr->getPrice() != 0) {
                        //3. Reemplazar por 
                        if ($unidad_peso == 'kg'){
                            $articulos[] = array(
                                'sku'           => $pr->getSku(),
                                'tipoProducto'  => 1,
                                'articulo'      => $pr->getName(),
                                'precio'        => $pr->getPrice(),
                                'pesoKg'        => $pr->getData($atributo_peso),
                                'pesoL'         => 0,
                                'pesoPV'        => 0    
                            );
                        }
                        if ($unidad_peso == 'l'){
                            $articulos[] = array(
                                'sku'           => $pr->getSku(),
                                'tipoProducto'  => 1,
                                'articulo'      => $pr->getName(),
                                'precio'        => $pr->getPrice(),
                                'pesoKg'        => 0,
                                'pesoL'         => $pr->getData($atributo_peso),
                                'pesoPV'        => 0 
                            );
                        }
                        if ($unidad_peso == 'pv'){
                            $articulos[] = array(
                                'sku'           => $pr->getSku(),
                                'tipoProducto'  => 1,
                                'articulo'      => $pr->getName(),
                                'precio'        => $pr->getPrice(),
                                'pesoKg'        => 0,
                                'pesoL'         => 0,
                                'pesoPV'        => $pr->getData($atributo_peso)
                            );
                        }    
                    }
                }
            }
            //Mage::log('Array de articulos - Pickit.php');
            //Mage::log($articulos);
            $direccionCliente = $datos["direccion"].'. '.$datos["localidad"];
            $response = $this->getApi()->iniciar(null)->obtenerCotizacion($direccionCliente,$articulos);
            //Mage::log('Llamada a Api realizada - Pickit.php');
            //Mage::log('Response URL: '.$response["Response"]["urlLightBox"]);
            if(isset($response["Response"]["urlLightBox"]) && isset($response["Response"]["cotizacionId"])) {
                Mage::getSingleton('core/session')->setUrlPickit($response["Response"]["urlLightBox"]);
                Mage::getSingleton('core/session')->setIdCotizacion($response["Response"]["cotizacionId"]);
            }
        }
        //Obtenemos method title desde configuracion y concatenamos.
        $methodtitle = $this->getPickitConfigData('carriers/pickitconfig/title');
        if($request->getFreeShipping() == true || $request->getPackageQty() == $this->getFreeBoxes()) {
            $price = 0;
            $methodtitle .= " - Envio Gratis";
        }
        $rate->setMethodTitle($methodtitle .' / Sucursal: '.$sucursal);
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getPickitConfigData('carriers/pickitconfig/title'));
        $rate->setMethod($this->_code);
        $rate->setPrice($price);

        Mage::getSingleton('core/session')->setEnvioPickit($datos);
        return $rate;
    }

    /**
    * Devuelve peso, volumen, valor declarado del envio
    */
    public function getShippingDetails($ship){
        $masAlto                    = 0;
        $masAncho                   = 0;
        $largoTotal                 = 0;
        $datos["peso"]              = 0;
        $datos["valorDeclarado"]    = 0;
        $datos["volumen"]           = 0;
        $datos["freeBoxes"]         = 0;
        $datos["bultos"]            = 0;
        $sku                        = '';
        //$tipoPedido                 = $this->getPickitConfigData('carriers/pickitconfig/paquete_tipo');
        // Tomamos el attr "medida" segun la configuracion del cliente
        if ($this->getPickitConfigData('carriers/pickitconfig/global_medida') == "") {
            $datos["medida"] = "gramos";
        } else {
            $datos["medida"] = $this->getPickitConfigData('carriers/pickitconfig/global_medida');
        }
        if ($datos["medida"]=="kilos") {
            $datos["medida"] = 1;
        } elseif ($datos["medida"]=="gramos") {
            $datos["medida"] = 1000;
        } else {
            $datos["medida"] = 1000;
        }
        foreach ($ship->getAllItems() as $_item) {
            if($sku != $_item->getSku()) {
                $sku                     = $_item->getSku();
                $price                   = floor($_item->getPrice());
                //Obtengo el atributo que usamos para peso de producto.
                $atributo_peso           = $this->getPickitConfigData('carriers/pickitconfig/atributo_peso');
                $datos["peso"]           = ($_item->getQty() * $_item->getData($atributo_peso) / $datos["medida"]) + $datos["peso"];
                $datos["valorDeclarado"] = ($_item->getQty() * $price) + $datos["valorDeclarado"];
                
                $product    = Mage::getModel('catalog/product')->loadByAttribute('sku', $_item->getSku(), array('paquete_largo','paquete_ancho','paquete_alto','cantidad_bultos'));
                $pkgQty     = (float)$product->getData('cantidad_bultos');
                $datos["bultos"] = $datos["bultos"] + ($pkgQty) ? $pkgQty : 1;
                
                // Si la condicion de free shipping está seteada en el producto
                if ($_item->getFreeShippingDiscount() && !$_item->getProduct()->isVirtual()) {
                    $datos["freeBoxes"] += $_item->getQty();
                }
            }
        }

        $datos["volumen"] = $masAlto * $masAncho * $largoTotal;

        return $datos;
        
    }

    /**
     * Devuelve el model api para hacer las llamadas
     */
    protected function getApi(){
        if(!$this->api) {
            $this->api = Mage::getModel('pickit/apicall');
        }
        return $this->api;
    }

    /**
     * Get config data for field
     *
     * @param string $field
     * @return string
     */
    protected function getPickitConfigData($field){
        return Mage::getStoreConfig($field,Mage::app()->getStore());
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods() {
        //Mage::log('Entrada a getAllowedMethods de Pickit');
        return array($this->_code    => $this->getPickitConfigData('name'));
    }
}
?>
