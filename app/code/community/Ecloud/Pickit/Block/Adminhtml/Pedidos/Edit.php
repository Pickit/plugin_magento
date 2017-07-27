<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Block_Adminhtml_Pedidos_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        //we assign the same blockGroup as the Grid Container
        $this->_blockGroup = 'pickit';
        //and the same controller
        $this->_controller = 'adminhtml_pedidos';
        //define the label for the save and delete button
        $this->_headerText = Mage::helper('pickit')->__('Edit Form');

        $this->_updateButton('save', 'label', Mage::helper('pickit')->__('Guardar'));
        
        //$this->_updateButton('delete', 'label', 'Delete');
        //$this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        $this->_mode = 'edit';
    }

    public function getHeaderText()
    {
        if( Mage::registry('order_data') OR Mage::registry('order_data')->getId() ) {
            return  $this->helper('pickit')->__("Ver el estado del pedido: #") . $this->htmlEscape(Mage::registry('order_data')->getData("id_orden")) . '<br />';
        } else {
            return $this->helper('pickit')->__("El administrador no puede agregar pedidos con el módulo de Pickit");
        }
    }

}
?>