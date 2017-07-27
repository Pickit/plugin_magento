<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?><?php 
class Ecloud_Pickit_Adminhtml_Tracking_PedidosController extends Mage_Adminhtml_Controller_Action
{
 
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('pickit/pedidos');
        $this->_addContent($this->getLayout()->createBlock('pickit/adminhtml_pedidos'));
        $this->renderLayout();
    }
}
?>