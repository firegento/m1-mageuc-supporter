<?php

class MageUC_Supporter_SupporterController extends Mage_Core_Controller_Front_Action
{
    /** @var array */
    private $validOutputFormats = ['html', 'json'];

    private function getOutputMethod()
    {
        $outputFormat = $this->getRequest()->getParam('format', 'json');
        if (!in_array($outputFormat, $this->validOutputFormats)) {
            $outputFormat = 'json';
        }

        return 'to' . ucfirst($outputFormat);
    }

    public function indexAction()
    {
        $this->_redirect('/');
    }

    public function listAction()
    {

        $outputMethod       = $this->getOutputMethod();
        $requestedSku       = $this->getRequest()->getParam('sku', 'mageunconf-2018');
        $providedCallback = $this->getRequest()->getParam('callback', false);
        $supporterListModel = Mage::getModel('mageuc_supporter/supporter');
        $supporterList      = $supporterListModel->getSupporterList($requestedSku);

        $supporterListBlock = Mage::app()
                                  ->getLayout()
                                  ->createBlock('mageuc_supporter/api_supporter_list', 'mageuc.api.supporter_list');
        $supporterListBlock->setData('supporter_list', $supporterList);
        $cachekey = implode('-', array(
            'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $supporterListBlock->getTemplateFile(),
            'template' => $supporterListBlock->getTemplate(),
            $requestedSku,
            $outputMethod
        ));

        $supporterListBlock->setCacheKey($cachekey);
        $output = $supporterListBlock->$outputMethod(['supporter_list']);

        $this->getResponse()->setHeader('content-type', 'text/html', true);
        if ('toJson' === $outputMethod) {
            $this->getResponse()->setHeader('content-type', 'application/json', true);
            if($providedCallback){
                $output = $providedCallback.'('.$output.')';
            }
        }
        $this->getResponse()->setBody($output);

    }
}
