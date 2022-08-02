<?php
declare(strict_types=1);

namespace Eleadtech\ProductAttachment\Model;

class Info extends \Magento\Framework\Model\AbstractModel implements \Eleadtech\ProductAttachment\Data\AttachmentsInterface\InfoInterface
{

    /**
     * @inheritdoc
     */
    public function getInputSku()
    {
        return $this->getData(self::KEY_INPUT_SKU);
    }

    /**
    * @inheritdoc
    */
    public function setInputSku($sku)
    {
        return $this->setData(self::KEY_INPUT_SKU, $sku);
    }
    /**
     * @inheritdoc
     */
    public function getAttachtments()
    {
        return $this->getData(self::KEY_ATTACHMENTS);
    }

    /**
     * @inheritdoc
     */
    public function setAttachtments($attachments)
    {
        return $this->setData(self::KEY_ATTACHMENTS, $attachments);
    }
}
