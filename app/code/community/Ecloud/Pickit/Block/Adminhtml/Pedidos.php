<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php
class Ecloud_Pickit_Block_Adminhtml_Pedidos extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'pickit';
        $this->_controller = 'adminhtml_pedidos';
        $this->_headerText = Mage::helper('adminhtml')->__('Estado de Pedidos de Pickit');
 
        parent::__construct();
        $this->_removeButton('add');
    }

}
?>
