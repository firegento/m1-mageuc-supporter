<?php

class MageUC_Supporter_Block_Api_Supporter_List extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if(!$this->getTemplate()){
            $this->setTemplate('mageuc/supporter/list.phtml');
        }
    }


    public function getCacheLifetime()
    {
        return 24*60*60;
    }

}
