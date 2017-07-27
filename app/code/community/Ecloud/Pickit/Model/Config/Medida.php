<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Medida
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'gramos', 'label'=>Mage::helper('adminhtml')->__('Gramos')),
            array('value' => 'kilos', 'label'=>Mage::helper('adminhtml')->__('Kg')),
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
            'gramos' => Mage::helper('adminhtml')->__('Gramos'),
            'kilos' => Mage::helper('adminhtml')->__('Kg'),
        );
    }

}
