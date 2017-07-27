<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Imposicion
{

   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'manual', 'label'=>Mage::helper('adminhtml')->__('Manual al realizar envio.')),
            array('value' => 'automatica', 'label'=>Mage::helper('adminhtml')->__('Automatica segun estado definido.')),
            array('value' => 'noimponer', 'label'=>Mage::helper('adminhtml')->__('No realizar imposicion.')),
        );
    }
}
