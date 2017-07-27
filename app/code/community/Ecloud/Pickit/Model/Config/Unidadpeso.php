<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Unidadpeso
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'kg', 'label'=>Mage::helper('adminhtml')->__('Kilogramos')),
            array('value' => 'l', 'label'=>Mage::helper('adminhtml')->__('Litros')),
            array('value' => 'pv', 'label'=>Mage::helper('adminhtml')->__('Peso Volumétrico'))
        );
    }

}
