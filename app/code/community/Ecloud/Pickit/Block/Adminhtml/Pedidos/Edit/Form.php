<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Block_Adminhtml_Pedidos_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'        => 'edit_form',
                'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method'    => 'post',
                'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('edit_form', array('legend'=>Mage::helper('pickit')->__('Datos del pedido')));
   
        $fieldset->addField('order_increment_id', 'text', array(
            'label'     => Mage::helper('pickit')->__('Pedido #'),
            'required'  => false,
            'name'      => 'order_increment_id',
            'readonly'  => true,
        ));

        $fieldset->addField('nombre', 'text', array(
            'label'     => Mage::helper('pickit')->__('Nombre de Cliente'),
            'required'  => false,
            'name'      => 'nombre',
        ));

       $fieldset->addField('apellido', 'text', array(
            'label'     => Mage::helper('pickit')->__('Apellido'),
            'required'  => false,
            'name'      => 'apellido',
            //'tabindex'  => 1,
        ));

        $fieldset->addField('telefono', 'text', array(
            'label'     => Mage::helper('pickit')->__('Telefono'),
            'required'  => false,
            'name'      => 'telefono',
        ));

        $fieldset->addField('dni', 'text', array(
            'label'     => Mage::helper('pickit')->__('DNI'),
            'required'  => false,
            'name'      => 'dni',
        ));
        
        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('pickit')->__('E-mail'),
            'required'  => false,
            'name'      => 'email',
        ));

        $fieldset->addField('provincia', 'text', array(
            'label'     => Mage::helper('pickit')->__('Provincia'),
            'required'  => false,
            'name'      => 'provincia',
        ));

        $fieldset->addField('localidad', 'text', array(
            'label'     => Mage::helper('pickit')->__('Localidad'),
            'required'  => false,
            'name'      => 'localidad',
        ));

        $fieldset->addField('cp_destino', 'text', array(
            'label'     => Mage::helper('pickit')->__('Codigo postal'),
            'required'  => false,
            'name'      => 'cp_destino',
        ));

        $fieldset->addField('direccion', 'text', array(
            'label'     => Mage::helper('pickit')->__('Direccion'),
            'required'  => false,
            'name'      => 'direccion',
        ));

        $fieldset->addField('valor_declarado', 'text', array(
            'label'     => Mage::helper('pickit')->__('Valor Declarado'),
            'required'  => false,
            'name'      => 'valor_declarado',
        ));

        $fieldset->addField('volumen', 'text', array(
            'label'     => Mage::helper('pickit')->__('Volumen'),
            'required'  => false,
            'name'      => 'volumen',
        ));

        $fieldset->addField('peso', 'text', array(
            'label'     => Mage::helper('pickit')->__('Peso'),
            'required'  => false,
            'name'      => 'peso',
        ));

        $fieldset->addField('precio', 'text', array(
            'label'     => Mage::helper('pickit')->__('Precio de Envio'),
            'required'  => false,
            'name'      => 'precio',
        ));

        $fieldset->addField('id_orden', 'text', array(
            'label'     => Mage::helper('pickit')->__('Id Orden'),
            'required'  => false,
            'name'      => 'id_orden',
            'readonly'  => true,
        ));

        $fieldset->addField('cod_tracking', 'text', array(
            'label'     => Mage::helper('pickit')->__('Codigo Tracking'),
            'required'  => false,
            'name'      => 'cod_tracking',
        ));

        $fieldset->addField('datos_sucursal', 'text', array(
            'label'     => Mage::helper('pickit')->__('Datos de la Sucursal'),
            'required'  => false,
            'name'      => 'datos_sucursal',
        ));

        $fieldset->addField('estado', 'text', array(
            'label'     => Mage::helper('pickit')->__('Estado del envio'),
            'required'  => false,
            'name'      => 'estado',
            'readonly'  => true,
        ));

        //muestro "tracking", los detalles del estado del envio.

        $fieldset->addField('tracking', 'textarea', array(
            'label'     => Mage::helper('pickit')->__('Detalles del Envio'),
            'required'  => false,
            'name'      => 'tracking',
            'readonly'  => true
        ));

        $fieldset->addField('id_cotizacion', 'text', array(
            'label'     => Mage::helper('pickit')->__('ID Cotizacion'),
            'required'  => false,
            'name'      => 'id_cotizacion',
        ));

        $fieldset->addField('id_transaccion', 'text', array(
            'label'     => Mage::helper('pickit')->__('ID Transaccion'),
            'required'  => false,
            'name'      => 'id_transaccion',
        ));

        if (Mage::registry('order_data')){
            $form->setValues(Mage::registry('order_data')->getData());
        }
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
?>