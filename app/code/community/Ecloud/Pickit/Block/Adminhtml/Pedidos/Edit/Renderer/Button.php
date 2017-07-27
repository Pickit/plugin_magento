<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Block_Adminhtml_Pedidos_Edit_Renderer_Button extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

  public function render(Varien_Object $row) {
    $columnaID     = $row->getId();
    $model         = Mage::getModel('pickit/order')->load($columnaID);
    $estadoenvio   = $model->getData('estado');
    $constanciaURL = $model->getData('constancia');
    $params        = array('row_id'=> $columnaID);
    $ordersurl     = Mage::helper('adminhtml')->getUrl("adminhtml/pickit_orders/envioImpreso",$params);
    
    if ($constanciaURL != '') {
      $html = "<a href='".$ordersurl."' target='_blank'><button>Imprimir Constancia</button></a>";
    }
    else{
      $html = '<span>No hay ninguna constancia para ser impresa.</span>';
      if ($estadoenvio != 'Enviado') {
        $html = $html . "El Pedido no ha sido Enviado.";
      }
    }
    
   return $html;

  }
}
?>
