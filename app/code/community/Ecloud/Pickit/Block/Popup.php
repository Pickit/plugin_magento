<?php
class Ecloud_Pickit_Block_Popup extends Mage_Core_Block_Template {
    public function getUrlPickit() {
        $url = '';
        if(Mage::getSingleton('core/session')->getUrlPickit())
            $url = Mage::getSingleton('core/session')->getUrlPickit();
        return $url;
    }
}
?>