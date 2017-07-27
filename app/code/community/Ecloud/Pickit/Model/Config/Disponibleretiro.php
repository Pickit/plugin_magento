<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_DisponibleRetiro
{
   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'imponer', 'label'=>Mage::helper('adminhtml')->__('Al imponer.')),
            array('value' => 'imprimir', 'label'=>Mage::helper('adminhtml')->__('Al imprimir etiqueta.')),
        );
    }
}
