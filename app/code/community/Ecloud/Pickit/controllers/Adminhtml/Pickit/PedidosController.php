<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Adminhtml_Pickit_PedidosController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
    	$this->_title($this->__('Pickit'))->_title($this->__('Estado de pedidos de Pickit'));
        $this->loadLayout();
        $this->_setActiveMenu('pickit/pickit');
        $this->_addContent($this->getLayout()->createBlock('pickit/adminhtml_pedidos'));
        $this->renderLayout();
    }

    public function gridAction()
    {
		$this->_title($this->__('Pickit'))->_title($this->__('Estado de pedidos'));
        $this->loadLayout();
        $this->_setActiveMenu('pickit/pickit');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('pickit/adminhtml_pedidos_grid')->toHtml()
        );
    }

    public function exportEcloudCsvAction()
    {
        $fileName = 'pedidos_pickit.csv';
        $grid = $this->getLayout()->createBlock('pickit/adminhtml_pickit_pedidos_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportEcloudExcelAction()
    {
        $fileName = 'pedidos_pickit.xml';
        $grid = $this->getLayout()->createBlock('pickit/adminhtml_pickit_pedidos_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function massEliminarAction()
	{
		$ids = $this->getRequest()->getParam('id');
		if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pickit')->__('Por favor seleccionar una orden!'));
		} else {
			try {
				foreach ($ids as $id) {
					//Mage::getModel('pickit/order')->load($id)->delete();
					Mage::getModel('pickit/order')->load($id)->setData("estado","Eliminada")->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pickit')->__('Se han eliminado %d registro(s).', count($ids)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massEntregadoAction()
	{
		$ids = $this->getRequest()->getParam('id');

		if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pickit')->__('Por favor seleccionar una orden!'));
		} else {
			try {
				date_default_timezone_set('America/Argentina/Buenos_Aires');
				$date = date('d/m/Y h:i:s A', time());
				foreach ($ids as $id) {
					Mage::getModel('pickit/order')->load($id)->setData("entrega",$date)->save();
					Mage::getModel('pickit/order')->load($id)->setData("estado","Entregado")->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pickit')->__('Se han actualizado %d registro(s).', count($ids)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massPendienteAction()
	{
		$ids = $this->getRequest()->getParam('id');
		if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pickit')->__('Por favor seleccionar una orden!'));
		} else {
			try {
				foreach ($ids as $id) {
					Mage::getModel('pickit/order')->load($id)->setData("estado","Pendiente")->save();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pickit')->__('Se han actualizado %d registro(s).', count($ids)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

    public function viewAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if ($id) {
            $order = Mage::getModel('pickit/order')->load($id);
            if (!$order || !$order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pickit')->__('No se encontró el ID de la orden'));
                $this->_redirect('*/*/');
            }
        }
        
        Mage::register('order_data', $order);
 
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('pickit/adminhtml_pedidos_edit');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
    }

    public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('pickit/order');
			$model->setData($data)->setId($this->getRequest()->getParam('id'));
			$model->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pickit')->__('El pedido fue editado con éxito.'));
			Mage::getSingleton('adminhtml/session')->setFormData(false);
		}
			
        $this->_redirect('*/*/');
	}

}
?>