<?php

class MageUC_Supporter_Model_Supporter extends Mage_Core_Model_Abstract
{
    const DATA_COLUMN_PRODUCT_OPTIONS = 'product_options';

    /** @var integer */
    private $supporterOptionId;

    private function getFilteredProductOptionCollection($product)
    {
        $options = Mage::getModel('catalog/product_option')->getProductOptionCollection($product);
        $options->addFieldToFilter('default_option_title.title', ['like' => '%supporter%'])
                ->clear()
                ->addValuesToResult();

        return $options;
    }


    private function getSupporterForSku($sku)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select     = $connection->select()
                                 ->from('sales_flat_order_item', self::DATA_COLUMN_PRODUCT_OPTIONS)
                                 ->where('sku = ?', $sku)
                                 ->where('sales_flat_order.status = "complete"')
                                 ->joinInner('sales_flat_order', 'sales_flat_order_item.order_id = sales_flat_order.entity_id', '');

        return $connection->query($select)->fetchAll(Zend_Db::FETCH_ASSOC);
    }

    private function getSupporterOptionId($sku)
    {
        if (!$this->supporterOptionId) {
            $product = Mage::getSingleton('catalog/product');
            $product->setId($product->getIdBySku($sku));

            $options = $this->getFilteredProductOptionCollection($product);
            $option  = $options->getFirstItem();

            $this->supporterOptionId = $option->getId();

        }

        return $this->supporterOptionId;
    }

    public function getSupporterList($sku)
    {
        $supporterList     = [];
        $supporterFromDb   = $this->getSupporterForSku($sku);
        $supporterOptionId = $this->getSupporterOptionId($sku);
        foreach ($supporterFromDb as $serializedSupporterData) {
            $supporterData = unserialize($serializedSupporterData[self::DATA_COLUMN_PRODUCT_OPTIONS]);
            if (array_key_exists($supporterOptionId,$supporterData['info_buyRequest']['options']) && mb_strlen($supporterData['info_buyRequest']['options'][$supporterOptionId]) > 0 &&'.' !== $supporterData['info_buyRequest']['options'][$supporterOptionId]) {
                array_push($supporterList, $supporterData['info_buyRequest']['options'][$supporterOptionId]);
            }
        }

        return $supporterList;
    }
}
