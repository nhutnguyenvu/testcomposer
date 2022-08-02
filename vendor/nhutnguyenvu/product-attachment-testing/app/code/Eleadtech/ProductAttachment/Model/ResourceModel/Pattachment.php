<?php
namespace Eleadtech\ProductAttachment\Model\ResourceModel;

class Pattachment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_attachment', 'entity_id');
    }

    public function removeAttachmentsByProductId($productId){
        $this->getConnection()->delete($this->getMainTable(),["product_id = ?"=>$productId]);
    }
    public function getDataByProductIdAndAttachmentId($productId, $attachmentId){
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable());
        $select->where('product_id = ?',$productId);
        $select->where('product_attachment_id = ?',$attachmentId);
        return  $this->getConnection()->fetchRow($select);
    }
    public function removeAttachmentByProductIdandAttachmentId($productId, $attachmentId){
        return $this->getConnection()->delete($this->getMainTable(),["product_id = ?"=>$productId, "product_attachment_id = ?"=>$attachmentId]);
    }
    public function updateRecord($record){
        $table = $this->getMainTable();
        $select = $this->getConnection()->select();
        $select->from($table)->where("product_id = ?",$record["product_id"])
            ->where("product_attachment_id = ?",$record["product_attachment_id"]);
        $result = $this->getConnection()->fetchRow($select);
        if(empty($result)){
            $this->getConnection()->insert($table, $record);
        }
        else{
            $where = ['product_id = ?' => $record['product_id'],'product_attachment_id = ?' => $record['product_attachment_id']];
            $this->getConnection()->update($table,$record,$where);
        }
    }
}
?>
