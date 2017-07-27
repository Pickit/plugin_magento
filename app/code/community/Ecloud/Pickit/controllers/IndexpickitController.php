<?php
class Ecloud_Pickit_IndexpickitController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		
	}

	public function setPuntoAction() {
		Mage::getSingleton('core/session')->setPuntoSeleccionado(true);
		return;
	}
}
?>