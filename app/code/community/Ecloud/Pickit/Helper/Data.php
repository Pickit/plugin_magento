<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	public function getTrackingpopup($tracking) {

	}
	*/
	
	/**
	* Devuelve los datos de la orden segun el metodo elegido en checkout
	* Si no es metodo pickit, devuelve false
	*/
	public function getPickitData($method){
		if(preg_match('/pickit/',$method)){
			$datos = Mage::getSingleton('core/session')->getEnvioPickit();
		}else{
			return false;
		}
		return $datos;
	}

    public function getRegionId($sucursal_region, $sucursal_city){

        $sucursal_city 	 = trim(strtoupper(str_replace(array('á','é','í','ó','ú'), array('a','e','i','o','u'), $sucursal_city)));
        $sucursal_region = trim(strtoupper(str_replace(array('á','é','í','ó','ú'), array('a','e','i','o','u'), $sucursal_region)));

        

        $regionMap = array(
                'CAPITAL FEDERAL'		=> 'C',
                'BUENOS AIRES'			=> 'B',
                'CATAMARCA'				=> 'K',
                'CHACO'					=> 'H',
                'CHUBUT'				=> 'U',
                'CORDOBA'				=> 'X',
                'CORRIENTES'			=> 'W',
                'ENTRE RIOS'			=> 'E',
                'FORMOSA'				=> 'P',
                'JUJUY'					=> 'Y',
                'LA PAMPA'				=> 'L',
                'LA RIOJA'				=> 'F',
                'MENDOZA'				=> 'M',
                'MISIONES'				=> 'N',
                'NEUQUEN'				=> 'Q',
                'RIO NEGRO'				=> 'R',
                'SALTA'					=> 'A',
                'SAN JUAN'				=> 'J',
                'SAN LUIS'				=> 'D',
                'SANTA CRUZ'			=> 'Z',
                'SANTA FE'				=> 'S',
                'SANTIAGO DEL ESTERO'   => 'G',
                'TIERRA DEL FUEGO'		=> 'V',
                'TUCUMAN'				=> 'T'
                );

		if ($sucursal_city == 'CIUDAD AUTONOMA DE BUENOS AIRES' || $sucursal_city == 'AUTONOMOUS CITY OF BUENOS AIRES') $sucursal_region =  'CAPITAL FEDERAL';
        if (! array_key_exists($sucursal_region, $regionMap) ) $sucursal_region =  'CAPITAL FEDERAL';

        $sucursal_region = $regionMap[$sucursal_region];

        $region = Mage::getModel('directory/region')->loadByCode($sucursal_region, 'AR');
        return $region->getId();

    }

}
?>