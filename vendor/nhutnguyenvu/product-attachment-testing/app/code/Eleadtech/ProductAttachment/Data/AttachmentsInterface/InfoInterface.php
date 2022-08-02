<?php
namespace Eleadtech\ProductAttachment\Data\AttachmentsInterface;


interface InfoInterface  extends  \Magento\Framework\Api\ExtensibleDataInterface{

    const KEY_INPUT_SKU = 'input_sku';
    const KEY_ATTACHMENTS = 'attachments';
    /**
     * Returns Index
     *
     * @return string|null Index
     */
    public function getInputSku();

    /**
     * Sets the product SKU.
     *
     * @param string $sku
     * @return $this
     */
    public function setInputSku($sku);

    /**
     * Returns Attachments
     *
     * @return \Magento\Framework\DataObject[] Attachments
     */
    public function getAttachtments();

    /**
     * Sets attachments.
     *
     * @param \Magento\Framework\DataObject[] $attachments
     * @return $this
     */
    public function setAttachtments($attachments);
}
