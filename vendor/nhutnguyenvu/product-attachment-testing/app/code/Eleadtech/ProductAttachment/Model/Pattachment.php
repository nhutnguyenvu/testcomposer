<?php
declare(strict_types=1);

namespace Eleadtech\ProductAttachment\Model;

class Pattachment extends \Magento\Framework\Model\AbstractModel implements \Eleadtech\ProductAttachment\Data\AttachmentsInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    const NONE_CUSTOM_PRICE = -1;
    protected function _construct()
    {
        $this->_init('Eleadtech\ProductAttachment\Model\ResourceModel\Pattachment');
    }
    public function removeAttachmentsByProductId($productId){
        $this->getResource()->removeAttachmentsByProductId($productId);
    }
    public function getAttachmentIdsByProductId($productId){
        $collection = $this->getCollection()->addFieldToFilter("product_id",$productId);
        $attachmentIds = [];
        if($collection){
            foreach ($collection as $item ){
                $attachmentIds[] = $item->getProductAttachmentId();
            }
            return $attachmentIds;
        }
        return [];
    }
    public function getDataByProductIdAndAttachmentId($productId,$attachmentId){
        return $this->_getResource()->getDataByProductIdAndAttachmentId($productId,$attachmentId);
    }
    public function removeAttachmentByProductIdandAttachmentId($productId,$attachmentId){
        return $this->getResource()->removeAttachmentByProductIdandAttachmentId($productId,$attachmentId);
    }
    public function updateRecord($data){
        return  $this->getResource()->updateRecord($data);
    }

    /**
     * @inheritdoc
     */

    public function getAttachmentsInfo()
    {
        return $this->getData(self::KEY_LIST);
    }

    /**
     * @inheritdoc
     */
    public function setAttachmentsInfo($attachmentInfo)
    {
        return $this->setData(self::KEY_LIST, $attachmentInfo);
    }

}
?>
