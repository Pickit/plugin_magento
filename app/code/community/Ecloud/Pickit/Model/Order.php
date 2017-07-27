<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php 

class Ecloud_Pickit_Model_Order extends Mage_Core_Model_Abstract
{
    
    protected function _construct()
    {
        $this->_init('pickit/order');
    }

    public function loadById($id){
        $collection = $this->getCollection()
                ->addFieldToFilter('id_orden', $id);
        return $collection->getFirstItem();
    }
    
    public function loadByOrderIncrementId($id){
        $collection = $this->getCollection()
                ->addFieldToFilter('order_increment_id', $id);
        return $collection->getFirstItem();
    }

    public function loadByTrackingCode($track){
	    $collection = $this->getCollection()
	            ->addFieldToFilter('cod_tracking', $track);
	    return $collection->getFirstItem();
	}
}
?>