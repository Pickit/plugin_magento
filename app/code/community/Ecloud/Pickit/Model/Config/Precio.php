<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Precio
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'automatico', 'label'=>Mage::helper('adminhtml')->__('Automático.')),
            array('value' => 'fijo', 'label'=>Mage::helper('adminhtml')->__('Fijo')),
            array('value' => 'porcentaje', 'label'=>Mage::helper('adminhtml')->__('Porcentaje Personalizado'))
        );
    }


}
