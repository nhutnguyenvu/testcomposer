<?php
namespace Eleadtech\ProductAttachment\Data;


interface AttachmentsInterface  extends  \Magento\Framework\Api\ExtensibleDataInterface{

    const KEY_LIST = 'list';


    /**
     * Returns Attachments List
     *
     * @return \Eleadtech\ProductAttachment\Data\AttachmentsInterface\InfoInterface[].
     */
    public function getAttachmentsInfo();

    /**
     * Sets the product quantity.
     *
     * @param \Eleadtech\ProductAttachment\Data\AttachmentsInterface\InfoInterface[] $attachmentInfo
     * @return $this
     */
    public function setAttachmentsInfo($attachmentInfo);
}
