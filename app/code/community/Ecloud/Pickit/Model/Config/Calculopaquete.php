<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Calculopaquete
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label'=>Mage::helper('adminhtml')->__('Un paquete por pedido')),
            array('value' => '2', 'label'=>Mage::helper('adminhtml')->__('Un paquete para cada producto'))
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
            '1' => Mage::helper('adminhtml')->__('Un paquete por pedido'),
            '2' => Mage::helper('adminhtml')->__('Un paquete para cada producto'),
        );
    }

}
