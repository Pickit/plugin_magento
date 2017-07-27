<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Apicall extends Mage_Core_Model_Abstract
{
    protected $_tokenId;
    protected $_storeId;
    /**
     * Cotiza el envio de los productos segun los parametros
     *
     * @param $params 
     * @return $result array tal cual pickit or null en error
     */

    public function _construct() {
        
    }

    public function iniciar($storeid) {
        if($storeid == null) {
            $this->_storeId = Mage::app()->getStore()->getStoreId();
        } else {
            $this->_storeId = $storeid;
        }
        //Mage::log("Store ID: ".$this->_storeId);
        $this->_tokenId = $this->getConfigData('carriers/pickitconfig/token_id');
        //Mage::log("Token ID: ".$this->_tokenId);
        //Mage::log('Constructor de Apicall - Token ID: '.$this->_tokenId);
        return $this;
    }

    protected function _callWS($metodo,$parametros) {

        if($metodo == "ObtenerDetalleTransaccion") {
            //Mage::log('Apikey Obtener Detalle Transaccion');
            $apikey = $this->getConfigData('carriers/pickitconfig/apikey_webapp');
        } else {
            $apikey = $this->getConfigData('carriers/pickitconfig/apikey');
        }
        //Mage::log('Entrada a _callWS - Apicall');
        //Mage::log('Apikey: '.$apikey);
        $ws = "";
        if($this->isTestMode()) {
            $ws = $this->getConfigData('carriers/pickitconfig/url_webservice_test');
        } else {
            $ws = $this->getConfigData('carriers/pickitconfig/url_webservice_prod');
        }
        //Mage::log('WS url: '.$ws);
        //Mage::log($parametros);
        $parametros = json_encode($parametros);
        
        $data = json_encode(array('ApiKey' => $apikey,
                                  'Metodo' => $metodo,
                                  'Parametros' => $parametros));
        
        $postdata = http_build_query(
            array(
                'value' => $data
            ), '', '&');

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context  = stream_context_create($opts);

        $result = file_get_contents($ws,false, $context);
        
        return json_decode($result,true);
    }

    public function obtenerCotizacion($direccion,$articulos) {
        //Mage::log('Obtener cotizacion - Apicall');
        $metodo = "ObtenerCotizacionEnvio";
        $params = array(
                'tokenId' => $this->_tokenId,
                'direccionCliente' => $direccion,
                'articulos' => $articulos
                );
        return $this->_callWS($metodo,$params);
    }

    public function obtenerInformacionPuntoSeleccionado($idCotizacion) {
        //Mage::log('Obtener informacion del punto seleccionado - Apicall');
        $metodo = "ObtenerInformacionPuntoSeleccionado";
        $params = array(
                'tokenId' => $this->_tokenId,
                'cotizacionId' => $idCotizacion
                );
        return $this->_callWS($metodo,$params);
    }

    public function generarEnvio($idCotizacion,$dataCliente,$numeroOrden) {
        //die(var_dump(Mage::app()->getStore()));
        //Mage::log('Generar envio - Apicall');
        //Siempre que se llame a este metodo, se realizara la imposicion
        $response = $this->imponerTransaccion($idCotizacion,$dataCliente,$numeroOrden);
        if($response == null) {
            Mage::getSingleton('core/session')->addError('PickIt - El WebService devolvio una respuesta nula - Verifique las credenciales en la configuracion.');
            return $response;
        } else {
            if($response["Response"]) {
                //Si el WS no nos dio una respuesta nula, tomamos el Tracking y lo guardamos en sesion
                Mage::getSingleton('core/session')->setTrackingPickit($response["Response"]["CodigoTransaccion"]);
                
                //Mage::log("Id Transaccion: ".$response["Response"]["TransaccionId"]);
                //Mage::log("Codigo Transaccion: ".$response["Response"]["CodigoTransaccion"]);
                
                //Tomamos el campo de la configuracion para ver en que momento se realiza el disponible para retiro
                $disponible = Mage::getStoreConfig('carriers/pickitconfig/disponibleretiro',$this->_storeId);
                //Mage::log('Disponible para retiro: '.$disponible);

                //Realizamos verificaciones en el valor de disponibleRetiro de la config
                if($disponible == 'imponer') {
                    $idTransaccion = $response["Response"]["TransaccionId"];
                    return $this->disponibleRetiro($idTransaccion); 
                } elseif($disponible == 'imprimir') {
                    //Si no es momento de realizar el disponibleRetiro, devolvemos la respuesa del ImponerTransaccionEnvio
                    return $response;
                }    
            } else {
                Mage::getSingleton('core/session')->addError('PickIt - '.$response["Status"]["Text"]);
                return $response;
            }
        }
    }

    public function imponerTransaccion($idCotizacion,$dataCliente,$numeroOrden) {
        //Mage::log('Imponer Transaccion - Apicall');
        $metodo = "ImponerTransaccionEnvio";

        //Ver despues como manejar la direccion alternativa
        $dataDireccionAlternativa = array('direccion' => '',
            'localidad' => '',
            'provinciaId' => '',
            'codigoPostal' => ''
            );

        $params = array(
                'tokenId' => $this->_tokenId,
                'cotizacionId' => $idCotizacion,
                'courierId' => '1',
                'motivoCambio' => null,
                'observaciones' => '',
                'numeroOrden' => $numeroOrden,
                'dataDireccionAlternativa' => $dataDireccionAlternativa,
                'tipoOperacion' => 1,
                'dataCliente' => $dataCliente
                );
        return $this->_callWS($metodo,$params);
    }

    public function disponibleRetiro($idTransaccion) {
        //Mage::log('Disponible para retiro - Apicall');
        $metodo = "DisponibleParaRetiro";
        $params = array(
                'tokenId' => $this->_tokenId,
                'transaccionId' => $idTransaccion
                );
        return $this->_callWS($metodo,$params);
    }

    public function obtenerDetalleTransaccion($idTransaccion) {
        //Mage::log('Obtener Detalle Transaccion - Apicall');
        $metodo = "ObtenerDetalleTransaccion";
        $params = array(
                'idTransaccion' => $idTransaccion
                );
        return $this->_callWS($metodo,$params);
    }


    /**
     * Corta el campo calle para no exceder el numero de caracteres limite
     */
    protected function _splitStreet($street, $limit = 30){
        $result     = array();
        $address    = is_array($street) ? implode(' ', $street) : $street;
        $html       = htmlentities($address, ENT_COMPAT, 'UTF-8');
        $html       = str_replace($this->htmlSpecialChars, '-', $html);
        $address    = preg_replace("/&([a-z])[a-z]+;/i", "$1", $html);
        $length     = $limit-1;
        $result[]   = substr($address, 0, $length);
        $result[]   = substr($address, $length);
        return $result;
    }

    /**
     * Chequea si esta habilitado el Modo testeo
     */
    public function isTestMode(){
        return (bool)$this->getConfigData('carriers/pickitconfig/global_testmode');
    }

    /**
     * Get store config for current store
     */
    protected function getConfigData($field){
        return Mage::getStoreConfig($field,$this->_storeId);
    }

    /**
     * Formatea el numero a 2 decimales
     */
    protected function format2Decimals($number){
        return number_format($number,2);
    }

    /**
     * Chequea si es array of arrays
     */
    public function isMulti($value){
        return (isset($value[0]));
    }

    protected $htmlSpecialChars = array(
        '&deg;', '&amp;', '&lt;', '&gt;', '&trade;', '&reg;', '&nbsp;', '&quot;', '&pound;', '&curren;',
        '&brvbar;', '&sect;', '&copy;', '&ordf;', '&laquo;', '&not;', '&shy;', '&macr;', '&plusmn;',
        '&sup2;', '&sup3;', '&micro;', '&para;', '&middot;', '&sup1;', '&ordm;', '&raquo;', '&frac14;',
        '&frac12;', '&frac34;', '&iquest;', '&THORN;', '&Scaron;', '&scaron;', '&fnof;', '&circ;', '&tilde;',
        '&upsih;', '&piv;', '&ensp;', '&emsp;', '&thinsp;', '&zwnj;', '&zwj;', '&lrm;', '&rlm;', '&ndash;','&mdash;',
        '&lsquo;', '&rsquo;', '&sbquo;', '&ldquo;', '&rdquo;', '&bdquo;', '&dagger;', '&Dagger;', '&bull;',
        '&hellip;', '&permil;', '&prime;', '&Prime;', '&lsaquo;', '&rsaquo;', '&frasl;', '&image;', '&weierp;',
        '&larr;', '&uarr;', '&rarr;', '&darr;', '&harr;', '&crarr;', '&lArr;', '&uArr;', '&rArr;', '&dArr;', '&hArr;',
        '&forall;', '&part;', '&empty;', '&isin;', '&notin;', '&ni;', '&prod;', '&sum;', '&minus;', '&lowast;',
        '&radic;', '&prop;', '&infin;', '&ang;', '&and;', '&or;', '&cap;', '&cup;', '&int;', '&there4;', '&sim;',
        '&cong;', '&asymp;', '&ne;', '&equiv;', '&le;', '&ge;', '&sub;', '&sup;', '&nsub;', '&sube;', '&supe;',
        '&oplus;', '&otimes;', '&perp;', '&sdot;', '&lceil;', '&rceil;', '&lfloor;', '&rfloor;', '&lang;', '&rang;',
        '&loz;', '&spades;', '&clubs;', '&hearts;', '&diams;'
    );
}
?>