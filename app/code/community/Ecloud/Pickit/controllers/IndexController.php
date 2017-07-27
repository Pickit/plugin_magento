<?php
class Ecloud_Pickit_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		
	}

	public function getPPAction() {
		//Mage::log('Entrada a controller para setear id punto pickit');
		$params = $this->getRequest()->getParams();
		if(isset($params["idPP"])) {
			Mage::getSingleton('core/session')->setIdPuntoPickit($params["idPP"]);
			echo $params["idPP"];
		} else {
			echo 0;
		}
	}

	public function setPuntoAction() {
		Mage::getSingleton('core/session')->setPuntoSeleccionado(true);
		return;
	}
}
?>