<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Franjahoraria
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label'=>Mage::helper('adminhtml')->__('de 8:00 a 17:00 hs')),
            array('value' => '2', 'label'=>Mage::helper('adminhtml')->__('de 8:00 a 12:00 hs')),
            array('value' => '3', 'label'=>Mage::helper('adminhtml')->__('de 14:00 a 17:00 hs')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '1' => Mage::helper('adminhtml')->__('de 8:00 a 17:00 hs'),
            '2' => Mage::helper('adminhtml')->__('de 8:00 a 12:00 hs'),
            '3' => Mage::helper('adminhtml')->__('de 14:00 a 17:00 hs'),
        );
    }

}
