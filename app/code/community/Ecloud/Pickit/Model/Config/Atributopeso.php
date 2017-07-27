<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Model_Config_Atributopeso
{
   /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
        foreach ($productAttrs as $prodattr){
            if ($prodattr->getFrontendLabel()){
                $_attributes[] = array('value' => $prodattr->getName(), 'label' => $prodattr->getFrontendLabel());
            }else{
                $_attributes[] = array('value' => $prodattr->getName(), 'label' => $prodattr->getName());
            }
        }
        return $_attributes;
    }

}
