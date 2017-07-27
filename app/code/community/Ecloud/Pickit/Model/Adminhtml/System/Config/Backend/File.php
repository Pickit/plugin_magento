<?php
class Ecloud_Pickit_Model_Adminhtml_System_Config_Backend_File extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    protected function _getUploadDir()
    {
        //Mage::log("Backend Model Ecloud - get upload dir");
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder(Mage::getBaseDir('media').DS.'pickit');
        
        $fieldConfig = $this->getFieldConfig();
        /* @var $fieldConfig Varien_Simplexml_Element */

        if (empty($fieldConfig->upload_dir)) {
            Mage::throwException(Mage::helper('catalog')->__('The base directory to upload file is not specified.'));
        }

        $uploadDir = (string)$fieldConfig->upload_dir;

        $el = $fieldConfig->descend('upload_dir');

        /**
         * Add scope info
         */
        if (!empty($el['scope_info'])) {
            $uploadDir = $this->_appendScopeInfo($uploadDir);
        }

        /**
         * Take root from config
         */
        if (!empty($el['config'])) {
            $uploadRoot = $this->_getUploadRoot((string)$el['config']);
            $uploadDir = $uploadRoot . '/' . $uploadDir;
        }
        return $uploadDir;
    }
}