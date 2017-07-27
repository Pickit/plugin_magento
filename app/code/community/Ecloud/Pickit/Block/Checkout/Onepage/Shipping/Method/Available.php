<?php
class Ecloud_Pickit_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Abstract {
	protected $_rates;
    protected $_address;

    public function getShippingRates()
    {

        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();
            /*
            if (!empty($groups)) {
                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter(Mage::app()->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }
    
	public function getUrlPickit() {
		//Mage::log('Entrada a getUrl - Rewrite block');
		$url = Mage::getSingleton('core/session')->getUrlPickit();
        //Mage::log('Url que se guarda en variable de sesion: '.$url);
		//Mage::getSingleton('core/session')->unsUrlPickit();
		return $url;
	}

    public function validarIdPuntoPickit() {
        if(Mage::getSingleton('core/session')->getIdPuntoPickit()) {
            return true;
        } else {
            return false;
        }
    }

    public function getLoaderUrl() {
        $loader = $this->getSkinUrl('css/pickit/loader.gif');
        if(Mage::getStoreConfig('carriers/pickitconfig/loader_pickit',Mage::app()->getStore()) != "") {
            $loader = Mage::getBaseUrl('media')."pickit/".Mage::getStoreConfig('carriers/pickitconfig/loader_pickit',Mage::app()->getStore());
        }
        //Mage::log('URL Loader: '.$loader);
        return $loader;
    }
}
?>