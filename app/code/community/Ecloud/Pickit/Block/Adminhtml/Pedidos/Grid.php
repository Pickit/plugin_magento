<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Block_Adminhtml_Pedidos_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('pickit_order');
        $this->setDefaultSort('order_increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('pickit/order')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        /*
        $this->addColumn('id', array(
            'header' => Mage::helper('pickit')->__('ID'),
            'sortable' => true,
            'width' => '5',
            'index' => 'id'
        ));*/
 
        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('pickit')->__('# Pedido'),
            'sortable' => true,
            'width' => '5',
            'index' => 'order_increment_id',
            'type'  => 'text'
        ));

        $this->addColumn('nombre', array(
            'header' => Mage::helper('pickit')->__('Nombre'),
            'sortable' => true,
            'width' => '5',
            'index' => 'nombre',
            'type'  => 'text'
        ));

        $this->addColumn('apellido', array(
            'header' => Mage::helper('pickit')->__('Apellido'),
            'sortable' => true,
            'width' => '5',
            'index' => 'apellido',
            'type'  => 'text'
        ));

        $this->addColumn('cod_tracking', array(
            'header' => Mage::helper('pickit')->__('Nro Pickit - Tracking'),
            'sortable' => true,
            'width' => '5',
            'index' => 'cod_tracking',
            'type'  => 'text'
        ));

        $this->addColumn('impresion', array(
			'header'=> Mage::helper('catalog')->__('Imprimir Constancia'),
			'sortable'  => false,
			'target' => '_blank',
            'width' => '5',
			'renderer'  => 'pickit/adminhtml_Pedidos_Edit_Renderer_button'
        ));

        $this->addColumn('id_cotizacion', array(
            'header' => Mage::helper('pickit')->__('ID Cotizacion'),
            'sortable' => true,
            'width' => '5',
            'index' => 'id_cotizacion',
            'type'  => 'text'
        ));

        $this->addColumn('id_transaccion', array(
            'header' => Mage::helper('pickit')->__('ID Transaccion'),
            'sortable' => true,
            'width' => '5',
            'index' => 'id_transaccion',
            'type'  => 'text'
        ));

        $this->addColumn('estado', array(
            'header'    => Mage::helper('pickit')->__('Estado'),
            'sortable'  => false,
            'width'     => '5',
            'index'     => 'estado',
            'type'      => 'options',
            'sortable'  => false,
            'options'   => array(
                'Eliminada'  => 'Eliminada',
                'En Retailer' => 'En Retailer',
                'Disponible para retiro' => 'Disponible para retiro',
                'Pendiente' => 'Pendiente',
                'Envio generado sin imposicion' => 'Envio generado sin imposicion'
            )
        ));
 
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        
        return $this;
    }

    public function getRowUrl($row)
    {
         return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }	
}
?>