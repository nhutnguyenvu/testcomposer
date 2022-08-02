<?php

namespace Eleadtech\ProductAttachment\Model\ResourceModel\Pattachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eleadtech\ProductAttachment\Model\Pattachment', 'Eleadtech\ProductAttachment\Model\ResourceModel\Pattachment');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

    public function groupAttachmentProduct(){

        $nameAttributeId = 73;

        $this->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(["group_qty" =>"GROUP_CONCAT(qty)",
                "group_price" =>"GROUP_CONCAT(price)", "group_price_type" =>"GROUP_CONCAT(price_type)", "group_product_attachment_id" =>"GROUP_CONCAT(product_attachment_id)","product_id"])
            ->join(["product" => $this->getConnection()->getTableName("catalog_product_entity")],"product.entity_id = main_table.product_id",["sku" => "product.sku"])
            ->join(["product_varchar"=>$this->getConnection()->getTableName("catalog_product_entity_varchar")],"product_varchar.entity_id = main_table.product_id AND product_varchar.attribute_id = $nameAttributeId",["name" =>"product_varchar.value"])
            ->group(['product_id']);
    }

    /*
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        return $this;
    }
    */
}
?>
