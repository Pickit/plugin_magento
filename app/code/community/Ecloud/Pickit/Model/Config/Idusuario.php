<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Idusuario
{
   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customer_attributes = Mage::getModel('customer/customer')->getAttributes();
        foreach ($customer_attributes as $attr){
            if ($attr->getFrontendLabel()){
                $_attributes[] = array('value' => $attr->getName(), 'label' => $attr->getFrontendLabel());
            }else{
                $_attributes[] = array('value' => $attr->getName(), 'label' => $attr->getName());
            }
        }
        return $_attributes;
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
