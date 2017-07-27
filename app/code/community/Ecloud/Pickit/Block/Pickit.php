<?php
/**
 * @version   0.1.0
 * @author    ecloud solutions http://www.ecloudsolutions.com <info@ecloudsolutions.com>
 * @copyright Copyright (C) 2010 - 2015 ecloud solutions ®
 */
?>
<?php
class Ecloud_Pickit_Block_Pickit extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface
{

    protected function _toHtml()
    {
		$html ='';
        $html .= 'pickit parameter1 = '.$this->getData('parameter1').'<br/>';
        $html .= 'pickit parameter2 = '.$this->getData('parameter2').'<br/>';
        $html .= 'pickit parameter3 = '.$this->getData('parameter3').'<br/>';
        $html .= 'pickit parameter4 = '.$this->getData('parameter4').'<br/>';
        $html .= 'pickit parameter5 = '.$this->getData('parameter5').'<br/>';
        return $html;
    }
	
}
?>